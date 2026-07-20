# Technical Architecture Blueprint — Laverie Nails Measurement System

This document defines the application structure, database schema, Eloquent models, and service-layer contract for the Laravel 12 application. It is intended as a canonical reference so that logic (especially classification and measurement code) lives in the Service layer and controllers remain thin.

---

## 1. Application Directory Structure

Follow a strict Service Layer pattern: controllers handle HTTP, Services handle business logic.

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── SizeStandardController.php        # CRUD for admin size metrics
│   │   │   └── CatalogController.php              # Direct CRUD for Laverie products
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   └── RegisterController.php
│   │   ├── HomeController.php
│   │   ├── PanduanController.php
│   │   ├── InputDataController.php
│   │   └── HasilKlasifikasiController.php        # Handles workflow submissions
│   ├── Middleware/
│   │   └── RoleMiddleware.php                    # Enforces role boundaries
│   └── Requests/
│       └── StoreMeasurementRequest.php           # Strict numeric (mm) validation
├── Models/
│   ├── User.php
│   ├── SizeStandard.php
│   ├── Measurement.php
│   └── NailCatalog.php
├── Policies/
│   └── MeasurementPolicy.php                     # Enforces record ownership
└── Services/
    └── NailClassifierService.php                 # L1/Manhattan classification engine
```

## 2. Database Schema & Migrations

The app uses SQLite locally and is compatible with MySQL/Postgres in production.

### 2.1 Table: users
Represents consumers and administrators. Laverie is the sole catalog vendor.

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->json('roles'); // ['user'] or ['admin']

    $table->rememberToken();
    $table->timestamps();
});
```

### 2.2 Table: size_standards
Canonical standard measurements for sizes `XS`, `S`, `M`, `L`.

```php
Schema::create('size_standards', function (Blueprint $table) {
    $table->id();
    $table->string('size_name')->unique(); // 'XS', 'S', 'M', 'L'
    $table->decimal('jempol', 4, 1);    // thumb
    $table->decimal('telunjuk', 4, 1);  // index
    $table->decimal('tengah', 4, 1);    // middle
    $table->decimal('manis', 4, 1);     // ring
    $table->decimal('kelingking', 4, 1);// pinky
    $table->decimal('tolerance', 3, 1)->default(1.0); // allowed variance
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### 2.3 Table: measurements
Stores immutable algorithm outputs. `user_id` nullable to allow guest journeys.

```php
Schema::create('measurements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
    $table->json('right_hand_data'); // { jempol, telunjuk, tengah, manis, kelingking }
    $table->json('left_hand_data')->nullable();
    $table->string('classified_size_right');
    $table->string('classified_size_left')->nullable();
    $table->decimal('confidence_score_right', 5, 2);
    $table->decimal('confidence_score_left', 5, 2)->nullable();
    $table->timestamps();
});
```

### 2.4 Table: nail_catalogs
Official single-vendor product inventory managed exclusively by Laverie Admins.

```php
Schema::create('nail_catalogs', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->json('images'); // array of uploaded file paths
    $table->decimal('price', 10, 2);
    $table->string('size'); // 'XS', 'S', 'M', 'L'
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

## 3. Eloquent Models & Casting

### 3.1 Model: User

Example casts and relations:

```php
protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'roles' => 'array',
];

public function measurements(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Measurement::class);
}

```

### 3.2 Model: Measurement

```php
protected $casts = [
    'right_hand_data' => 'array',
    'left_hand_data' => 'array',
    'confidence_score_right' => 'decimal:2',
    'confidence_score_left' => 'decimal:2',
];

public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(User::class);
}
```

## 4. Service Layer: NailClassifierService

The classification logic must live in `app/Services/NailClassifierService.php` to ensure testability and reproducibility.

```php
namespace App\Services;

use App\Models\SizeStandard;

class NailClassifierService
{
    /**
     * Classify a single hand input using L1 (Manhattan) distance.
     *
     * @param array $inputData ['jempol' => float, 'telunjuk' => float, ...]
     * @return array ['size' => 'M'|'XS'|'S'|'L'|'Custom', 'confidence' => 95.00]
     */
    public function classifyHand(array $inputData): array
    {
        // 1. Fetch active standards: SizeStandard::where('is_active', true)->get()
        // 2. For each standard, compute L1 distance:
        //    diff = sum(abs(input[finger] - standard[finger]))
        // 3. Track minimal diff and corresponding standard
        // 4. confidence = max(0, 100 - (5 * min_diff))
        // 5. If min_diff > standard->tolerance then size = 'Custom'
        // 6. return ['size' => $sizeLabel, 'confidence' => $confidence]
    }
}
```

## 5. Security & Access Control

Authorization is enforced at both the HTTP layer (middleware/requests) and the model layer (policies).

| Route Prefix | Allowed Roles | Enforcer |
|--------------|---------------|----------|
| /admin/*     | admin         | RoleMiddleware('admin') |
| /riwayat/*   | user,admin    | MeasurementPolicy |

Catalog rules:
- `/admin/catalogs/*` is direct Admin CRUD; no moderation state exists.
- `/produk` and `/produk/{catalog}` query only rows where `is_active = true`.
- Catalog records have no user/vendor foreign key because Laverie is the sole vendor.

Example policy snippet:

```php
// app/Policies/MeasurementPolicy.php
public function view(User $user, Measurement $measurement): bool
{
    return $user->id === $measurement->user_id || in_array('admin', $user->roles);
}
```

---

If anything in this reformatted document should remain stricter (for example: literal size names, exact tolerance math, or migration column types), tell me which part to lock down and I will update the file accordingly.
