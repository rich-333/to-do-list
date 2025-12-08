-- Script para crear datos de prueba en LISTAS
-- Usa esto si necesitas datos de prueba sin tener que crear listas manualmente

-- Primero, obtén el ID del usuario autenticado
-- (Reemplaza 1 por tu user_id si es diferente)

INSERT INTO task_lists (user_id, title, created_at, updated_at) VALUES
(1, 'Mercado', NOW(), NOW()),
(1, 'Compras en línea', NOW(), NOW()),
(1, 'Tareas del hogar', NOW(), NOW());

-- Obtén los IDs de las listas creadas
-- SELECT id FROM task_lists WHERE user_id = 1;

-- Luego crea items para cada lista
INSERT INTO task_list_items (task_list_id, name, completed, `order`, created_at, updated_at) VALUES
-- Items para "Mercado" (ID 1)
(1, 'Pan', 0, 0, NOW(), NOW()),
(1, 'Leche', 0, 1, NOW(), NOW()),
(1, 'Huevos', 0, 2, NOW(), NOW()),
(1, 'Queso', 0, 3, NOW(), NOW()),

-- Items para "Compras en línea" (ID 2)
(2, 'Monitor 27 pulgadas', 0, 0, NOW(), NOW()),
(2, 'Teclado mecánico', 0, 1, NOW(), NOW()),
(2, 'Mouse inalámbrico', 0, 2, NOW(), NOW()),

-- Items para "Tareas del hogar" (ID 3)
(3, 'Limpiar la cocina', 0, 0, NOW(), NOW()),
(3, 'Lavar ropa', 0, 1, NOW(), NOW()),
(3, 'Pasar la aspiradora', 0, 2, NOW(), NOW());
