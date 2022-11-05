<?php

namespace App\Models;

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
        'subscription_id', 'name', 'url', 'text',
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

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the subscription this digest is for.
     */
    public function subscription() {
        return $this->belongsTo(Subscription::class);
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

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
