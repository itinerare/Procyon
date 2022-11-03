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
        'name', 'url', 'text',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'digests';

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
     * @return bool
     */
    public function createDigests() {
        foreach (config('subscriptions') as $feed) {
            // Read feed contents
            $feedContents = FeedReader::read($feed);

            // Find the date of the last digest for this feed,
            // so we can fetch only updates made since then
            $lastDigest = $this->where('url', $feed)->orderBy('created_at', 'DESC')->first();
            if ($lastDigest) {
                $startAt = $lastDigest->created_at;
            } else {
                // If there is no existing digest, just start at yesterday
                $startAt = Carbon::yesterday();
            }

            // Iterate through entries, taking only ones created after the start time
            $digestItems = [];
            foreach ($feedContents->get_items() as $item) {
                if (Carbon::parse($item->get_date()) > $startAt) {
                    // Items are keyed by date/time so that they can be sorted in chronological order
                    $digestItems[$item->get_date()] = [
                        'title'    => $item->get_title(),
                        'date'     => $item->get_date(),
                        'contents' => $item->get_content(),
                        'link'     => $item->get_link(),
                    ];
                }
            }
            ksort($digestItems);

            if(count($digestItems)) {
                $digestItems = array_map(function ($item) {
                    return '<div style="margin-bottom:.5em;"><h4><a href="'.$item['link'].'">'.$item['title'].'</a><br/>'.$item['date'].'</h4> '.$item['contents'].'</div>';
                }, $digestItems);

                $this->create([
                    'name' => $feedContents->get_title(),
                    'url' => $feed,
                    'text' => '<h1>'.$feedContents->get_title().' Digest for '.Carbon::today()->toFormattedDateString().'</h1>'.implode('', $digestItems),
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
