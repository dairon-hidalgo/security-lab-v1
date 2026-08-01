CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tickets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'abierto',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, full_name, role)
VALUES
    ('admin', 'admin123', 'Administrador FIIS', 'admin'),
    ('analista', 'fiis2026', 'Analista de Soporte', 'analyst'),
    ('usuario', 'password', 'Usuario de Prueba', 'user')
ON CONFLICT (username) DO NOTHING;

INSERT INTO tickets (user_id, title, description, status)
SELECT id, 'Problema de acceso', 'No puedo ingresar al sistema académico.', 'abierto'
FROM users
WHERE username = 'usuario'
AND NOT EXISTS (
    SELECT 1 FROM tickets WHERE title = 'Problema de acceso'
);

INSERT INTO tickets (user_id, title, description, status)
SELECT id, 'Actualización de software', 'Se requiere actualizar una estación de trabajo.', 'en proceso'
FROM users
WHERE username = 'analista'
AND NOT EXISTS (
    SELECT 1 FROM tickets WHERE title = 'Actualización de software'
);
