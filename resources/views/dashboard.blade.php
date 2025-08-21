<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>A2A KG</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@1.5.10/css/pico.min.css" rel="stylesheet">
  <style> pre{white-space:pre-wrap} .grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));} </style>
</head>
<body class="container">
  <main>
    <h1>A2A KG</h1>
    @if(session('status'))
      <article class="contrast">{{ session('status') }}</article>
    @endif

    <section>
      <h3>Add Source (crawl a start URL)</h3>
      <form method="POST" action="{{ route('sources.store') }}">
        @csrf
        <div class="grid">
          <label>Label<input name="label" value="URL" required></label>
          <label>Start URL<input name="start_url" placeholder="https://example.com" required></label>
          <label>Depth<input name="depth" type="number" value="1" min="0" max="5"></label>
          <label>Pages<input name="pages" type="number" value="1" min="1" max="500"></label>
        </div>
        <button type="submit">Crawl</button>
      </form>
    </section>

    <section>
      <h3>Q&A</h3>
      <form method="POST" action="{{ route('qa.ask') }}">
        @csrf
        <input name="q" placeholder='e.g., Why is "Order Service" related to "Payment Gateway"?' required>
        <button type="submit">Ask</button>
      </form>
      <details open>
        <summary>Recent Answers</summary>
        @foreach($answers as $a)
          <small>{{ $a->created_at->diffForHumans() }}</small>
          <pre>{{ $a->answer }}</pre>
        @endforeach
      </details>
    </section>

    <section class="grid">
      <article>
        <h4>Entities (latest 25)</h4>
        <ul>
          @foreach($entities as $e)
            <li>{{ $e->name }} <small>({{ $e->type }})</small></li>
          @endforeach
        </ul>
      </article>
      <article>
        <h4>Triples (latest 50)</h4>
        <ul>
          @foreach($triples as $t)
            <li>
              {{ optional($t->subject)->name ?? $t->subject_id }}
              — <strong>{{ $t->predicate }}</strong> →
              {{ optional($t->object)->name ?? $t->object_id }}
            </li>
          @endforeach
        </ul>
      </article>
      <article>
        <h4>Sources</h4>
        <ul>
          @foreach($sources as $s)
            <li>{{ $s->label }} <small>{{ $s->type }}</small></li>
          @endforeach
        </ul>
      </article>
      <article>
        <h4>Documents (latest 10)</h4>
        <ul>
          @foreach($docs as $d)
            <li><small>{{ $d->title }}</small> — <a href="{{ $d->external_id }}" target="_blank">{{ $d->external_id }}</a></li>
          @endforeach
        </ul>
      </article>
    </section>

  </main>
</body>
</html>
