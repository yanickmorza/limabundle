<div class="card shadow border-lima">
    <div class="card-body">
        {{ form_start(form) }}
            {{ form_widget(form) }}
            <button class="btn btn-success">Enregistrer</button>
            <a href="{{ path('<?= $route_name ?>_index') }}" class="btn btn-warning">Fermer</a>
        {{ form_end(form) }}
    </div>
</div>
