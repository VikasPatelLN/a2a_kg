
{{-- resources/views/graphops/admin.blade.php --}}
@extends('layouts.app') {{-- or your base layout --}}

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>GraphOps – Rebuild</span>
                <small id="statusSmall" class="text-muted"></small>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <pre id="status" class="mb-0">Loading…</pre>
                </div>
                <div class="d-flex gap-2">
                    <button id="start" class="btn btn-primary">Start Rebuild</button>
                    <button id="cancel" class="btn btn-outline-danger">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const statusEl = document.getElementById('status');
        const statusSmall = document.getElementById('statusSmall');
        const startBtn = document.getElementById('start');
        const cancelBtn = document.getElementById('cancel');

        async function refreshStatus() {
            try {
                const r = await fetch("{{ route('graphops.status') }}", {headers: {'Accept': 'application/json'}});
                const j = await r.json();
                statusEl.textContent = `State: ${j.state}
Last run: ${j.last_run_at ?? '—'}
Duration: ${j.last_duration ?? 0}s
Message: ${j.message ?? ''}`;
                statusSmall.textContent = `State: ${j.state}`;
                startBtn.disabled = (j.state === 'running') || (await isRunning());
            } catch (e) {
                statusEl.textContent = 'Failed to load status';
            }
        }

        async function isRunning() {
            // best-effort: rely on status only
            const r = await fetch("{{ route('graphops.status') }}", {headers: {'Accept': 'application/json'}});
            const j = await r.json();
            return j.state === 'running';
        }

        startBtn.onclick = async () => {
            if (!confirm('This will rebuild the graph. Continue?')) return;
            startBtn.disabled = true;
            try {
                const r = await fetch("{{ route('graphops.rebuild') }}", {method: 'POST', headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    }});
                const j = await r.json();
                alert(j.message || (r.ok ? 'Started' : 'Failed'));
            } catch (e) {
                alert('Failed to start rebuild');
            } finally {
                refreshStatus();
            }
        };

        cancelBtn.onclick = async () => {
            try {
                const r = await fetch("{{ route('graphops.cancel') }}", {method: 'POST', headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    }});
                const j = await r.json();
                alert(j.message || (r.ok ? 'Cancel signal sent' : 'Failed'));
            } catch (e) {
                alert('Failed to send cancel');
            } finally {
                refreshStatus();
            }
        };

        setInterval(refreshStatus, 3000);
        refreshStatus();
    </script>
@endsection
