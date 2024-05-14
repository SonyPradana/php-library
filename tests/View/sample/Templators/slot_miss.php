{% extend('Slots/layout.php') %}

{% section('content') %}
<p>{{ $product }}</p>
{% endsection %}

{% section('header') %}
<i>{{ $year }}</i>
{% endsection %}
