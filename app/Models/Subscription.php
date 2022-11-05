<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vedmant\FeedReader\Facades\FeedReader;

class Subscription extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url', 'last_entry',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscriptions';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_entry' => 'datetime',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get digests for this subscription.
     */
    public function digests() {
        return $this->hasMany(Digest::class);
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Generates digests for configured feeds.
     *
     * @param bool                $summaryOnly
     * @param \Carbon\Carbon|null $startAt
     *
     * @return bool
     */
    public function createDigests($summaryOnly = true, $startAt = null) {
        foreach ($this->all() as $subscription) {
            // Read feed contents
            $feedContents = FeedReader::read($subscription->url);

            if (!isset($startAt)) {
                if ($subscription->last_entry) {
                    // The time zone of the feed may not line up with that of this
                    // app, and in fact likely won't. Consequently, it makes more sense
                    // to start at the timestamp of the last entry in the prior digest
                    // than to start at the time of its creation.
                    $startAt = $subscription->last_entry;
                } else {
                    // Otherwise, just start at the prior day
                    $startAt = Carbon::yesterday();
                }
            }

            // Iterate through entries, taking only ones created after the start time
            $digestItems = [];
            foreach ($feedContents->get_items() as $item) {
                $itemDate = Carbon::parse($item->get_date());
                if ($itemDate > $startAt) {
                    // Items are keyed by date/time so that they can be sorted in chronological order
                    $digestItems[$itemDate->toJSON()] = [
                        'title'    => $item->get_title(),
                        'date'     => $itemDate,
                        'contents' => $item->get_content(),
                        'link'     => $item->get_link(),
                    ];
                }
                unset($itemDate);
            }
            ksort($digestItems);

            if (count($digestItems)) {
                $digestContents = array_map(function ($item) use ($summaryOnly) {
                    if ($summaryOnly) {
                        return '<div style="margin-bottom:.5em;"><h4><a href="'.$item['link'].'">'.($item['title'] ?? 'Untitled Post').'</a> ('.$item['date'].')</h4></div>';
                    }

                    return '<div style="margin-bottom:.5em;"><h4><a href="'.$item['link'].'">'.($item['title'] ?? 'Untitled Post').'</a><br/>'.$item['date'].'</h4> '.$item['contents'].'</div>';
                }, $digestItems);

                $subscription->digests()->create([
                    'name'       => $feedContents->get_title(),
                    'url'        => $subscription->url,
                    'text'       => '<h1><a href="'.$feedContents->get_link().'">'.$feedContents->get_title().'</a> Digest for '.Carbon::today()->toFormattedDateString().'</h1>'.implode('', $digestContents),
                ]);

                $subscription->update([
                    'last_entry' => end($digestItems)['date'],
                ]);
            }
        }

        return true;
    }
}
