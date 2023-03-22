{% section('Slots/layout.php', 'title') %}
{{ title }}
{% endsection %}


{% section('Slots/layout.php', 'content') %}
<p>{{ product }}</p>
{% endsection %}

{% section('Slots/layout.php', 'header') %}
<i>{{ year }}</i>
{% endsection %}
