<?php

namespace App\Models;

use Carbon\Carbon;
use FeedReader;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class Digest extends Model implements Feedable {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'url', 'text', 'last_entry',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'digests';

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

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Generates digests for configured feeds.
     *
     * @param bool $summaryOnly
     *
     * @return bool
     */
    public function createDigests($summaryOnly = true) {
        foreach (config('subscriptions') as $feed) {
            // Read feed contents
            $feedContents = FeedReader::read($feed);

            // Find the date of the last digest for this feed,
            // so we can fetch only updates made since then
            $lastDigest = $this->where('url', $feed)->orderBy('created_at', 'DESC')->first();
            if ($lastDigest) {
                // The time zone of the feed may not line up with that of this
                // app, and in fact likely won't. Consequently, it makes more sense
                // to start at the timestamp of the last entry in the prior digest
                // than to start at the time of its creation.
                $startAt = $lastDigest->last_entry ?? $lastDigest->created_at;
            } else {
                // If there is no existing digest, just start at yesterday
                $startAt = Carbon::yesterday();
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
                        return '<div style="margin-bottom:.5em;"><h4><a href="'.$item['link'].'">'.$item['title'].'</a> ('.$item['date'].')</h4></div>';
                    }

                    return '<div style="margin-bottom:.5em;"><h4><a href="'.$item['link'].'">'.$item['title'].'</a><br/>'.$item['date'].'</h4> '.$item['contents'].'</div>';
                }, $digestItems);

                $this->create([
                    'name'       => $feedContents->get_title(),
                    'url'        => $feed,
                    'text'       => '<h1>'.$feedContents->get_title().' Digest for '.Carbon::today()->toFormattedDateString().'</h1>'.implode('', $digestContents),
                    'last_entry' => end($digestItems)['date'],
                ]);
            }
        }

        return true;
    }

    public function toFeedItem(): FeedItem {
        return FeedItem::create([
            'id'         => $this->id,
            'title'      => $this->name,
            'summary'    => $this->text,
            'updated'    => $this->updated_at,
            'link'       => $this->url,
            'authorName' => $this->name,
        ]);
    }

    public static function getFeedItems() {
        return self::all();
    }
}
