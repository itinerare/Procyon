@extends('layouts.app')

@section('content')
    @if ($allowAccess)
        <div class="card">
            <div class="card-header text-center">
                <h4>Subscriptions</h4>
            </div>
            <div class="card-body">
                <p>
                    Here you can manage the feeds that Procyon is subscribed to. Note that it's recommended to add new feeds in completely new rows rather than replacing the URLs in old rows, as errors might otherwise ensue.
                </p>

                {!! Form::open(['url' => 'subscriptions']) !!}
                <div id="feedList">
                    @foreach ($subscriptions as $subscription)
                        <div class="input-group mb-3">
                            {!! Form::text('url[' . $subscription->id . ']', $subscription->url, [
                                'class' => 'form-control',
                                'placeholder' => 'Feed URL',
                                'aria-label' => 'Feed URL',
                            ]) !!}
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    Last Digest: @if ($subscription->digests->count())
                                        &nbsp;<abbr data-toggle="tooltip"
                                            title="{{ $subscription->digests()->first()->created_at }}">{{ $subscription->digests()->first()->created_at->diffForHumans() }}</abbr>
                                    @else
                                        Never!
                                    @endif
                                </span>
                                <a href="#" class="btn btn-danger remove-feed">
                                    ✕
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div>
                    <div class="float-right">
                        {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                    </div>
                    <a href="#" class="btn btn-primary" id="add-feed">Add Feed</a>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="feed-row d-none">
            <div class="input-group mb-3">
                {!! Form::text('url[]', null, [
                    'class' => 'form-control',
                    'placeholder' => 'Feed URL',
                    'aria-label' => 'Feed URL',
                ]) !!}
                <div class="input-group-append">
                    <a href="#" class="btn btn-danger remove-feed">
                        ✕
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="text-center align-self-center">
            {!! Form::open(['url' => 'subscriptions/password', 'method' => 'post']) !!}
            <div class="row">
                <div class="col-md">
                </div>
                <div class="col-md-6" style="margin-top:25vh;">
                    <div class="form-group">
                        {!! Form::password('password', [
                            'class' => 'form-control',
                            'placeholder' => 'Please enter the password.',
                            'aria-label' => 'Password entry',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md">
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $(document).ready(function() {
                $('#add-feed').on('click', function(e) {
                    e.preventDefault();
                    addFeedRow();
                });

                $('.remove-feed').on('click', function(e) {
                    e.preventDefault();
                    removeFeedRow($(this));
                })

                function addFeedRow() {
                    var $clone = $('.feed-row').clone();
                    $('#feedList').append($clone);
                    $clone.removeClass('d-none feed-row');
                    $clone.find('.remove-feed').on('click', function(e) {
                        e.preventDefault();
                        removeFeedRow($(this));
                    })
                }

                function removeFeedRow($trigger) {
                    $trigger.parent().parent().remove();
                }
            });
        }, false);
    </script>
@endsection
