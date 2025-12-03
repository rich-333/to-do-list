<!doctype html>
<html>
  <body>
    <h1>Recordatorio de evento: {{ $event->titulo }}</h1>
    <p>{{ $event->descripcion }}</p>
    <p>Inicio: {{ $event->inicio }}</p>
    @if(!empty($event->fin))
      <p>Fin: {{ $event->fin }}</p>
    @endif
    @if(!empty($event->ubicacion))
      <p>Lugar: {{ $event->ubicacion }}</p>
    @endif
  </body>
</html>
<!-- English template removed; using Spanish fields: titulo, descripcion, inicio, fin, ubicacion -->
