# API REST Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
La API usa tokens Sanctum para autenticación.

## Endpoints

### 1. Register
**POST** `/register`

**Body:**
```json
{
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
    "message": "Usuario registrado. Revisa tu email para verificar.",
    "user": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "created_at": "2024-01-27T10:00:00.000000Z",
        "updated_at": "2024-01-27T10:00:00.000000Z"
    }
}
```

### 2. Login
**POST** `/login`

**Body:**
```json
{
    "login": "john@example.com", // o "johndoe"
    "password": "password123"
}
```

**Response (200):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "email_verified_at": "2024-01-27T10:05:00.000000Z"
    },
    "token": "1|abc123def456..."
}
```

**Response (401):**
```json
{
    "message": "Credenciales inválidas"
}
```

**Response (403):**
```json
{
    "message": "Email no verificado"
}
```

### 3. Logout
**POST** `/logout`

**Headers:**
```
Authorization: Bearer 1|abc123def456...
```

**Response (200):**
```json
{
    "message": "Sesión cerrada"
}
```

### 4. Verify Email
**GET** `/email/verify/{id}/{hash}`

**Response (200):**
```json
{
    "message": "Email verificado correctamente"
}
```

**Response (400):**
```json
{
    "message": "Enlace inválido"
}
```

### 5. Get User Info
**GET** `/user`

**Headers:**
```
Authorization: Bearer 1|abc123def456...
```

**Response (200):**
```json
{
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "email_verified_at": "2024-01-27T10:05:00.000000Z"
}
```

## Error Responses

### Validation Error (422)
```json
{
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
}
```

## Usage Examples

### Register and Login Flow
```bash
# 1. Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "username": "johndoe", 
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# 2. Login (después de verificar email)
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "login": "john@example.com",
    "password": "password123"
  }'

# 3. Access protected route
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer 1|abc123def456..."
```

## Notes
- El login acepta email o username
- Se requiere verificación de email para poder hacer login
- Los tokens expiran después de 1 año por defecto
- Usa `username` o `email` en el campo `login`
