<html>


<body style="padding: 3em;">

<h1>Semantic Schema</h1>
<p>This page shows a view of all <a href ="#types"><strong><code>types</code></strong></a> and <a href ="#Properties"><strong><code>properties</code></strong></a> available in the current database tables of your Semantic Schema installation.</p>
<p>If there is no data, you should run the <code>migrations</code>.</p>
<p>See the Github repository <a href="https://github.com/iebele/semantic-schema">semantic-schema</a> for more information.</p>

<hr>

<h2>Main types</h2>
@foreach($data['mainTypes'] as $type)
    <h3 id="{{ $type['name'] }}"><a href="#{{ $type['name'] }}">{{ $type['name'] }}</a></h3>
    <div style="padding-left: 3em;">
        <p><strong>Description</strong>: <em>{{ $type['description'] }}</em></p>
        <p><strong>Canonical source</strong>: <a href="{{ $type['url'] }}">{{ $type['url']  }}</a></p>
        <p>
            <button onclick="showProperties('{{ $type['name'] }}', 'mainproperties_{{ $type['name'] }}');">Show properties</button>
            <ul id="mainproperties_{{ $type['name'] }}"></ul>
        </p>
        <h4>Parent</h4>
        @foreach($type['parents'] as $parent)
            <ul><li><a href="#{{$parent}}">{{$parent}}</a></li></ul>
        @endforeach
        <h4>Childs</h4>
        <ul>
            @foreach($type['childs'] as $child)
                <li><a href="#{{$child}}">{{$child}}</a></li>
            @endforeach
        </ul>
    </div>
@endforeach

<hr>

<h2 id="Types">All types</h2>
@foreach($data['types'] as $type)

    <h3 id="{{ $type['name'] }}"><a href="#{{ $type['name'] }}">{{ $type['name'] }}</a></h3>
    <div style="padding-left: 3em;">
        <p><strong>Description</strong>: <em>{{ $type['description'] }}</em></p>
        <p><strong>Canonical source</strong>: <a href="{{ $type['url'] }}">{{ $type['url']  }}</a></p>
        <button onclick="showProperties('{{ $type['name'] }}', 'properties_{{ $type['name'] }}');">Show properties</button>
        <ul id="properties_{{ $type['name'] }}"></ul>
        <h4>Parent</h4>
        @foreach($type['parents'] as $parent)
            <ul><li><a href="#{{$parent}}">{{$parent}}</a></li></ul>
        @endforeach
        <h4>Childs</h4>
        <ul>
        @foreach($type['childs'] as $child)
            <li><a href="#{{$child}}">{{$child}}</a></li>
        @endforeach
        </ul>
    </div>
@endforeach

<hr>

<h2 id="Properties">Properties</h2>
@foreach($data['properties'] as $property)

    <h3><a href="#{{ $property->name }}">{{ $property->name }}</a></h3>
    <div style="padding-left: 3em;">
        <p><strong>Description</strong>: <em>{{ $property->description }}</em></p>
        <p><strong>Canonical source</strong>: <a href="{{ $property->url}}">{{ $property->url}}</a></p>
    </div>
@endforeach

<!-- Get properties of a type with an AJAX call -->
<!-- Loading all properties for each type at once might exhaust resources  -->
<script>
    function getAjax(url, success) {
        var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
        xhr.open('GET', url);
        xhr.onreadystatechange = function() {
            if (xhr.readyState>3 && xhr.status==200) success(xhr.responseText);
        };
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send();
        return xhr;
    }

    function showProperties( typeName, element){

        var url = "/semantic-schema/api/type/" + typeName + "/properties";
        getAjax(url, function(data){
            obj = JSON.parse(data);
            var ul = document.getElementById(element);
            console.log(element);
            for (x in obj) {
                var li = document.createElement("li");
                li.innerHTML = "<a href='#>" + obj[x][0].name + "'>" + obj[x][0].name  + "</a><br><em>" + obj[x][0].description  + "</em>";
                ul.appendChild(li);
            }
        });
        console.log(typeName)
    }
</script>
</body>
</html>