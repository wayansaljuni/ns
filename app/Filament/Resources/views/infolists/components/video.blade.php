@if ($state)
    <video width="320" height="240" controls>
        <source src="{{ Storage::url($state) }}" type="video/mp4">
        Your browser does not support the video tag.
    </video>
@else
    <p>Tidak ada video.</p>
@endif