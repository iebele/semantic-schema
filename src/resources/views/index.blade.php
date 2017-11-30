<html>


<body>

<h1>Semantic Schema</h1>
<p>This page shows a view of all <a href ="#types"><strong><code>types</code></strong></a> and <a href ="#Properties"><strong><code>properties</code></strong></a> available in the current database tables of your Semantic Schema installation.</p>
<p>If there is no data, you should run the <code>migrations</code>.</p>
<p>See the Github repository <a href="https://github.com/iebele/semantic-schema">semantic-schema</a> for more information.</p>

<hr>


<h2 id="Types">Types</h2>
@foreach($data['types'] as $type)

    <h3><a href="/semantic-schema/type/{{ $type->name }}">{{ $type->name }}</a></h3>
    <div style="padding-left: 3em;">
        <p><strong>Description</strong>: <em>{{ $type->description }}</em></p>
        <p><strong>Canonical source</strong>: <a href="{{ $type->url}}">{{ $type->url}}</a></p>
    </div>
@endforeach

<hr>

<h2 id="Properties">Properties</h2>
@foreach($data['properties'] as $property)

    <h3><a href="/semantic-schema/property/{{ $property->name }}">{{ $property->name }}</a></h3>
    <div style="padding-left: 3em;">
        <p><strong>Description</strong>: <em>{{ $property->description }}</em></p>
        <p><strong>Canonical source</strong>: <a href="{{ $property->url}}">{{ $property->url}}</a></p>
    </div>
@endforeach

</body>
</html>