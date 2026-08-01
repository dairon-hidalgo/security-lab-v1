CREATE TABLE IF NOT EXISTS sqli_audit (
    id BIGSERIAL PRIMARY KEY,
    supplied_id TEXT NOT NULL,
    executed_query TEXT NOT NULL,
    result_count INTEGER NOT NULL DEFAULT 0,
    error_message TEXT,
    client_ip VARCHAR(64),
    user_agent TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, full_name, role)
VALUES
    ('soporte1', 'soporte123', 'Carlos Mendoza', 'support'),
    ('soporte2', 'clave2026', 'Lucía Fernández', 'support'),
    ('docente', 'docente123', 'Docente de Prueba', 'teacher'),
    ('estudiante', 'unas2026', 'Estudiante de Prueba', 'student')
ON CONFLICT (username) DO NOTHING;