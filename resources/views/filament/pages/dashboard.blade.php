<x-filament-panels::page>
    <div class="dashboard-shell">
        <section class="dashboard-hero">
            <div>
                <p class="dashboard-eyebrow">Ringkasan Hari Ini</p>
                <h2 class="dashboard-title">Dashboard {{ $this->roleLabel }}</h2>
                <p class="dashboard-subtitle">Data disesuaikan dengan hak akses akun yang sedang login.</p>
            </div>
        </section>

        <section class="dashboard-cards">
            @foreach ($this->statsCards as $card)
                <article class="dashboard-card">
                    <p class="dashboard-card-label">{{ $card['label'] }}</p>
                    <p class="dashboard-card-value">{{ $card['value'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="dashboard-section">
            <div class="dashboard-section-head">
                <h3>Transaksi Terbaru</h3>
            </div>

            @if (count($this->recentTransactions) === 0)
                <p class="dashboard-empty">Belum ada transaksi yang dapat ditampilkan untuk role ini.</p>
            @else
                <div class="dashboard-table-wrap">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>No. Transaksi</th>
                                <th>Kasir</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->recentTransactions as $row)
                                <tr>
                                    <td>{{ $row['transaction_number'] }}</td>
                                    <td>{{ $row['cashier'] }}</td>
                                    <td>{{ $row['status'] }}</td>
                                    <td>{{ $row['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <style>
        .dashboard-shell {
            display: grid;
            gap: 1rem;
        }

        .dashboard-hero {
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, rgba(14, 116, 144, 0.14), rgba(20, 184, 166, 0.08));
        }

        .dashboard-eyebrow {
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.75;
            margin: 0;
        }

        .dashboard-title {
            margin: 0.25rem 0 0;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .dashboard-subtitle {
            margin: 0.35rem 0 0;
            opacity: 0.75;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
        }

        .dashboard-card {
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 0.9rem;
            padding: 0.9rem 1rem;
            background: rgba(255, 255, 255, 0.02);
        }

        .dashboard-card-label {
            margin: 0;
            font-size: 0.8rem;
            opacity: 0.72;
        }

        .dashboard-card-value {
            margin: 0.25rem 0 0;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .dashboard-section {
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 0.9rem;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.02);
        }

        .dashboard-section-head {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }

        .dashboard-section-head h3 {
            margin: 0;
            font-size: 1rem;
        }

        .dashboard-empty {
            margin: 0;
            padding: 1rem;
            opacity: 0.72;
        }

        .dashboard-table-wrap {
            overflow-x: auto;
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .dashboard-table th,
        .dashboard-table td {
            text-align: left;
            padding: 0.7rem 1rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            white-space: nowrap;
        }

        .dashboard-table tbody tr:last-child td {
            border-bottom: 0;
        }
    </style>
</x-filament-panels::page>
