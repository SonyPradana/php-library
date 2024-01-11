{% extend('Slots/layout.php') %}

{% section('title') %}
{{ $title }}
{% endsection %}

{% section('content') %}
<p>{{ $product }}</p>
{% endsection %}

{% section('header') %}
<i>{{ $year }}</i>
{% endsection %}
