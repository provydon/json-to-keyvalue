<div class="json-keyvalue-display" style="font-family: system-ui, -apple-system, sans-serif;">
    @foreach($items as $index => $item)
        <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
            @if($label)
                <h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 600; color: #374151;">
                    {{ count($items) > 1 ? "$label #" . ($index + 1) : $label }}
                </h3>
            @endif
            
            <div style="display: grid; gap: 8px;">
                @foreach($item as $key => $value)
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px; padding: 8px; background: white; border-radius: 4px; border: 1px solid #e5e7eb;">
                        <div style="font-weight: 500; color: #6b7280; font-size: 13px;">
                            {{ $key }}
                        </div>
                        <div style="color: #111827; font-size: 13px; word-break: break-word;">
                            {{ $value }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

