{% section('Slots/layout2.php', 'title') %}
{{ title }} | website
{% endsection %}

{% section('Slots/layout2.php', 'body') %}
    {% php %}
        $correct = true;
    {% endphp %}

    {% include('component.php') %}
    {% if ($correct === true) %}
        <ul>
            {% foreach products as product %}
                <li>{{ product }}</li>
            {% endforeach %}
        </ul>
    {% endif %}
{% endsection %}

