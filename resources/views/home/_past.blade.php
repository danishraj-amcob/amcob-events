<section class="blk" id="past" style="padding-top:10px">
    <div class="wrap">
        <div class="sec-head">
            <div>
                <span class="eyebrow">Recap</span>
                <h2 class="sec-t">Past events</h2>
                <p class="sec-s">Relive the highlights from recent AMCOB gatherings.</p>
            </div>
        </div>
        <div class="egrid">
            @forelse ($pastEvents as $event)
                @include('events._card', ['past' => true])
            @empty
                <p class="sec-s" style="grid-column:1/-1;padding:40px 0;text-align:center">No past events yet.</p>
            @endforelse
        </div>
    </div>
</section>
