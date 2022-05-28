<div class="card shadow border-lima">
    <div class="card-body">
        {{ form_start(form) }}
            {{ form_widget(form) }}
            {% if is_show == false %}
                <button class="btn btn-success">{{ edit == 'Enregistrer' ? 'Modifier' : 'Enregistrer' }}</button>
            {% else %}
                <a href="{{ path('<?= $route_name ?>_edit', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>}) }}" class="btn btn-success">Editer</a>
            {% endif %}
                <a href="{{ path('<?= $route_name ?>_index') }}" class="btn btn-warning">Fermer</a> 
        {{ form_end(form) }}
    </div>
</div>

