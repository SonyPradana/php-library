{% extend('Slots/layout2.php') %}

{% section('title') %}
{{ $title }} | website
{% endsection %}

{% section('body') %}
    {% php %}
        $correct = true;
    {% endphp %}

    {% include('component.php') %}
    {% if ($correct === true) %}
        <ul>
            {% foreach $products as $product %}
                <li>{{ $product }}</li>
            {% endforeach %}
        </ul>
    {% endif %}
{% endsection %}

