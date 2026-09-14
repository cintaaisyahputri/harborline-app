<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $order->order_number }} — HARBORLINE PROVISIONS</title>
    <style>
        :root {
            --teal: #0B7A75;
            --abyss: #1A3A3A;
            --cream: #F4F1DE;
            --halftone: #E9C46A;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--abyss);
            font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
            color: var(--abyss);
            display: flex;
            justify-content: center;
            padding: 2.5rem 1rem;
        }

        .receipt {
            width: 100%;
            max-width: 380px;
            background: var(--cream);
            border-radius: 4px;
            padding: 1.75rem 1.5rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
        }

        .brand {
            text-align: center;
            border-bottom: 2px dashed var(--teal);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .brand .mark {
            font-size: 1.75rem;
            margin-bottom: 0.25rem;
        }

        .brand h1 {
            font-family: 'Georgia', 'Playfair Display', serif;
            font-style: italic;
            font-size: 1.3rem;
            margin: 0;
            color: var(--teal);
        }

        .brand small {
            display: block;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            font-size: 0.65rem;
            color: var(--abyss);
            opacity: 0.65;
            margin-top: 0.35rem;
        }

        .meta {
            font-size: 0.8rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .meta .row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .meta .label {
            opacity: 0.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.78rem;
            margin-bottom: 1rem;
        }

        thead th {
            text-align: left;
            border-bottom: 1px dashed var(--abyss);
            padding-bottom: 0.4rem;
            opacity: 0.6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.05em;
        }

        thead th:last-child, td.num { text-align: right; }

        tbody td {
            padding: 0.4rem 0 0.4rem 0;
            vertical-align: top;
            border-bottom: 1px solid rgba(26, 58, 58, 0.1);
        }

        tbody td.item-name { font-weight: 600; }
        tbody td.item-sub { display: block; font-weight: 400; opacity: 0.55; font-size: 0.7rem; }

        .totals {
            border-top: 2px dashed var(--teal);
            padding-top: 0.75rem;
            font-size: 0.85rem;
        }

        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 0.15rem 0;
        }

        .totals .grand {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--teal);
            margin-top: 0.4rem;
        }

        .status-pill {
            display: inline-block;
            margin-top: 0.6rem;
            padding: 0.2rem 0.75rem;
            border-radius: 999px;
            background: var(--teal);
            color: var(--cream);
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.7rem;
            opacity: 0.6;
        }

        .footer .waves { color: var(--halftone); margin-bottom: 0.3rem; }

        .print-btn {
            display: block;
            width: 100%;
            max-width: 380px;
            margin: 1rem auto 0;
            padding: 0.75rem;
            background: var(--halftone);
            color: var(--abyss);
            border: none;
            border-radius: 999px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-size: 0.8rem;
            cursor: pointer;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .receipt { box-shadow: none; max-width: 100%; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div>
        <div class="receipt">
            <div class="brand">
                <div class="mark">⚓</div>
                <h1>Harborline Provisions</h1>
                <small>Maritime Food Supply Chain</small>
            </div>

            <div class="meta">
                <div class="row"><span class="label">Receipt No.</span><span>{{ $order->order_number }}</span></div>
                <div class="row"><span class="label">Buyer</span><span>{{ $order->buyer->name }}</span></div>
                <div class="row"><span class="label">Warehouse</span><span>{{ $order->warehouse->name }}</span></div>
                <div class="row"><span class="label">Location</span><span>{{ $order->warehouse->location }}</span></div>
                <div class="row"><span class="label">Issued</span><span>{{ now()->format('d M Y, H:i') }}</span></div>
                @if ($order->fulfilled_at)
                    <div class="row"><span class="label">Fulfilled</span><span>{{ $order->fulfilled_at->format('d M Y, H:i') }}</span></div>
                @endif
                <div><span class="status-pill">{{ $order->status }}</span></div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty (kg)</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $line)
                        <tr>
                            <td class="item-name">
                                {{ $line->inventoryItem->name ?? 'Item removed' }}
                                <span class="item-sub">{{ $line->inventoryItem->sku ?? '—' }} · ${{ number_format($line->unit_price, 2) }}/kg</span>
                            </td>
                            <td class="num">{{ number_format($line->quantity_kg, 2) }}</td>
                            <td class="num">${{ number_format($line->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals">
                <div class="row"><span>Subtotal</span><span>${{ number_format($order->total_amount, 2) }}</span></div>
                <div class="row grand"><span>Total</span><span>${{ number_format($order->total_amount, 2) }}</span></div>
            </div>

            @if ($order->notes)
                <div class="meta" style="margin-top: 1rem;">
                    <span class="label">Notes:</span> {{ $order->notes }}
                </div>
            @endif

            <div class="footer">
                <div class="waves">〜 〜 〜 〜 〜 〜 〜 〜 〜 〜</div>
                Thank you for provisioning with us. Fair winds!
            </div>
        </div>

        <button class="print-btn" onclick="window.print()">Print this receipt</button>
    </div>
</body>
</html>
