INSERT INTO users (username, password, full_name, role)
VALUES
    ('mesa01',       'mesa123',       'Andrea Torres',       'support'),
    ('mesa02',       'soporte2026',   'Miguel Salazar',      'support'),
    ('mesa03',       'helpdesk01',    'Daniela Rojas',       'support'),
    ('tecnico01',    'tecnico123',    'Jorge Ramirez',       'technician'),
    ('tecnico02',    'redes2026',     'Paola Castro',        'technician'),
    ('tecnico03',    'hardware01',    'Renato Mendoza',      'technician'),
    ('docente01',    'docente123',    'Julio Paredes',       'teacher'),
    ('docente02',    'clases2026',    'Mariana Vega',        'teacher'),
    ('docente03',    'fiisdocente',   'Fernando Ruiz',       'teacher'),
    ('alumno01',     'alumno123',     'Pedro Flores',        'student'),
    ('alumno02',     'universidad',   'Valeria Soto',        'student'),
    ('alumno03',     'sistemas2026',  'Diego Navarro',       'student'),
    ('alumno04',     'password123',   'Camila Herrera',      'student'),
    ('secretaria01', 'secretaria',    'Rosa Medina',         'staff'),
    ('laboratorio',  'laboratorio',   'Encargado de Lab',    'staff'),
    ('coordinador',  'coord123',      'Carlos Zamora',       'coordinator'),
    ('auditor',      'auditoria2026', 'Auditor de Seguridad','auditor'),
    ('invitado01',   'guest',         'Usuario Invitado Uno','guest'),
    ('invitado02',   'guest123',      'Usuario Invitado Dos','guest'),
    ('pruebas',      'test123',       'Cuenta de Pruebas',   'tester')
ON CONFLICT (username) DO NOTHING;