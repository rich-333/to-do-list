<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recordatorio de Evento</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #0b74de; color: white; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px 20px; }
        .event-info { background: #f9f9f9; padding: 15px; border-radius: 6px; border-left: 4px solid #0b74de; margin: 15px 0; }
        .event-info p { margin: 8px 0; }
        .label { font-weight: bold; color: #0b74de; }
        .footer { background: #f0f0f0; padding: 15px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Recordatorio de Evento</h1>
        </div>
        
        <div class="content">
            <p>Hola,</p>
            <p>Te recordamos que tienes el siguiente evento próximo:</p>
            
            <div class="event-info">
                <p><span class="label">Evento:</span> {{ $event->titulo }}</p>
                <p><span class="label">Fecha y hora:</span> {{ optional($event->inicio)->format('d/m/Y H:i') }}</p>
                @if($event->ubicacion)
                    <p><span class="label">Ubicación:</span> {{ $event->ubicacion }}</p>
                @endif
                @if($event->descripcion)
                    <p><span class="label">Descripción:</span> {{ $event->descripcion }}</p>
                @endif
            </div>
            
            <p>¡No olvides asistir a tiempo!</p>
        </div>
        
        <div class="footer">
            <p>Este es un recordatorio automático de OrganizerAI</p>
        </div>
    </div>
</body>
</html>
