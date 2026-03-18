@props([
    'cancel' => null,
    'saveLabel' => 'Save',
])

<div class="flex gap-3">
    <button type="submit" class="btn-primary">{{ $saveLabel }}</button>
    @if($cancel)
        <a href="{{ $cancel }}" class="btn-secondary">Cancel</a>
    @endif
</div>
