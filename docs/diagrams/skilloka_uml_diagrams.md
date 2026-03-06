# Skilloka - Diagram UML untuk Dokumen SDD & SRS

> Dokumen ini berisi seluruh diagram teknis sistem Skilloka untuk kelengkapan laporan Software Design Document (SDD) dan Software Requirements Specification (SRS).

---

## 1. Use Case Diagram

```mermaid
graph TB
    subgraph "Sistem Skilloka"
        UC1["🔍 Mencari LPK / Kursus"]
        UC2["📋 Melihat Detail Kursus"]
        UC3["📋 Melihat Detail LPK"]
        UC4["📝 Booking Kursus"]
        UC5["💳 Melakukan Pembayaran"]
        UC6["⭐ Memberikan Review"]
        UC7["📜 Melihat Sertifikat"]
        UC8["🔐 Login / Register"]
        UC9["👤 Mengelola Profil"]
        UC10["📍 Filter Lokasi"]

        UC11["📊 Melihat Dashboard"]
        UC12["📚 Mengelola Kursus"]
        UC13["👥 Mengelola Siswa"]
        UC14["📋 Mengelola Booking"]
        UC15["✅ Verifikasi Kehadiran"]

        UC16["🏛️ Dashboard Super Admin"]
        UC17["🏢 Mengelola Tenant/LPK"]
        UC18["📂 Mengelola Kategori"]
        UC19["✅ Verifikasi LPK"]
    end

    Peserta["👤 Peserta/User"]
    Admin["👨‍💼 Admin LPK"]
    SuperAdmin["🛡️ Super Admin"]

    Peserta --> UC8
    Peserta --> UC1
    Peserta --> UC2
    Peserta --> UC3
    Peserta --> UC4
    Peserta --> UC5
    Peserta --> UC6
    Peserta --> UC7
    Peserta --> UC9
    Peserta --> UC10

    Admin --> UC8
    Admin --> UC11
    Admin --> UC12
    Admin --> UC13
    Admin --> UC14
    Admin --> UC15

    SuperAdmin --> UC16
    SuperAdmin --> UC17
    SuperAdmin --> UC18
    SuperAdmin --> UC19

    UC4 -.->|include| UC5
    UC6 -.->|include| UC4
    UC7 -.->|include| UC4
```

### Tabel Deskripsi Use Case

| No | Use Case | Aktor | Deskripsi |
|----|----------|-------|-----------|
| UC1 | Mencari LPK/Kursus | Peserta | Mencari LPK atau kursus berdasarkan keyword, kategori, lokasi |
| UC2 | Melihat Detail Kursus | Peserta | Melihat informasi lengkap kursus: silabus, jadwal, harga, ulasan |
| UC3 | Melihat Detail LPK | Peserta | Melihat profil LPK: fasilitas, kursus tersedia, rating |
| UC4 | Booking Kursus | Peserta | Mendaftar ke jadwal kursus yang tersedia |
| UC5 | Melakukan Pembayaran | Peserta | Membayar via VA Bank atau E-Wallet |
| UC6 | Memberikan Review | Peserta | Memberi penilaian & komentar setelah kursus selesai |
| UC7 | Melihat Sertifikat | Peserta | Mengunduh sertifikat kelulusan kursus |
| UC8 | Login/Register | Semua | Autentikasi via OTP telepon atau social login |
| UC9 | Mengelola Profil | Peserta | Mengubah data diri, foto, lokasi |
| UC10 | Filter Lokasi | Peserta | Filter berdasarkan kecamatan Indramayu |
| UC11 | Melihat Dashboard | Admin LPK | Melihat statistik booking, pendapatan, siswa |
| UC12 | Mengelola Kursus | Admin LPK | CRUD kursus dan jadwal |
| UC13 | Mengelola Siswa | Admin LPK | Melihat dan mengelola data peserta kursus |
| UC14 | Mengelola Booking | Admin LPK | Melihat, mengubah status booking peserta |
| UC15 | Verifikasi Kehadiran | Admin LPK | Scan QR Code untuk verifikasi kehadiran peserta |
| UC16 | Dashboard Super Admin | Super Admin | Melihat overview seluruh LPK dan sistem |
| UC17 | Mengelola Tenant/LPK | Super Admin | Mengelola pendaftaran dan status LPK |
| UC18 | Mengelola Kategori | Super Admin | CRUD kategori kursus |
| UC19 | Verifikasi LPK | Super Admin | Memverifikasi legalitas dan kelayakan LPK |

---

## 2. ERD (Entity Relationship Diagram)

```mermaid
erDiagram
    TENANTS {
        uuid id PK
        string domain UK
        string name
        json settings
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    LOCATIONS {
        bigint id PK
        bigint parent_id FK
        string name
        enum level "province|regency|district"
        string code UK
        decimal lat
        decimal long
        timestamp created_at
        timestamp updated_at
    }

    USERS {
        bigint id PK
        uuid tenant_id FK
        string name
        string email UK
        string phone UK
        string avatar
        bigint location_id FK
        string fcm_token
        string password
        timestamp email_verified_at
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    LPKS {
        uuid id PK
        uuid tenant_id FK
        string name
        string legal_name
        string nib
        bigint location_id FK
        text address
        decimal lat
        decimal long
        text description
        json facilities
        boolean is_verified
        enum status "pending|active|suspended"
        json contact_info
        string logo
        json images
        timestamp created_at
        timestamp updated_at
    }

    LPK_VERIFICATIONS {
        bigint id PK
        uuid lpk_id FK
        bigint verified_by FK
        enum type "dinas|internal"
        text notes
        json documents
        timestamp verified_at
        timestamp created_at
        timestamp updated_at
    }

    CATEGORIES {
        uuid id PK
        string name
        string slug UK
        string icon
        string color
        integer order
        timestamp created_at
        timestamp updated_at
    }

    COURSES {
        uuid id PK
        uuid tenant_id FK
        uuid lpk_id FK
        uuid category_id FK
        string title
        string slug
        text description
        text syllabus
        decimal price
        enum level "beginner|intermediate|advanced"
        integer duration_hours
        string cert_type
        json images
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    COURSE_SCHEDULES {
        uuid id PK
        uuid course_id FK
        date start_date
        date end_date
        time daily_start
        time daily_end
        integer max_capacity
        string days_of_week
        enum status "open|closed|cancelled"
        timestamp created_at
        timestamp updated_at
    }

    BOOKINGS {
        uuid id PK
        uuid tenant_id FK
        bigint user_id FK
        uuid schedule_id FK
        string code UK
        enum status "pending|paid|cancelled|completed"
        decimal amount
        string qr_code
        timestamp expires_at
        timestamp created_at
        timestamp updated_at
    }

    PAYMENTS {
        uuid id PK
        uuid booking_id FK
        string method
        string provider
        string external_id
        decimal amount
        enum status "pending|success|failed"
        timestamp paid_at
        json metadata
        timestamp created_at
        timestamp updated_at
    }

    REVIEWS {
        bigint id PK
        bigint user_id FK
        uuid course_id FK
        integer rating
        text comment
        json images
        boolean is_verified_purchase
        timestamp created_at
        timestamp updated_at
    }

    CERTIFICATES {
        uuid id PK
        bigint user_id FK
        uuid course_id FK
        uuid booking_id FK
        string number UK
        string file_url
        date issue_date
        date expiry_date
        timestamp created_at
        timestamp updated_at
    }

    TENANTS ||--o{ USERS : "memiliki"
    TENANTS ||--o{ LPKS : "memiliki"
    TENANTS ||--o{ COURSES : "memiliki"
    TENANTS ||--o{ BOOKINGS : "memiliki"

    LOCATIONS ||--o{ LOCATIONS : "parent"
    LOCATIONS ||--o{ USERS : "berlokasi di"
    LOCATIONS ||--o{ LPKS : "berlokasi di"

    LPKS ||--o{ COURSES : "menyediakan"
    LPKS ||--o{ LPK_VERIFICATIONS : "diverifikasi"

    USERS ||--o{ BOOKINGS : "melakukan"
    USERS ||--o{ REVIEWS : "menulis"
    USERS ||--o{ CERTIFICATES : "memiliki"
    USERS ||--o{ LPK_VERIFICATIONS : "memverifikasi"

    CATEGORIES ||--o{ COURSES : "mengkategorikan"

    COURSES ||--o{ COURSE_SCHEDULES : "memiliki jadwal"
    COURSES ||--o{ REVIEWS : "menerima"
    COURSES ||--o{ CERTIFICATES : "menghasilkan"

    COURSE_SCHEDULES ||--o{ BOOKINGS : "dibooking"

    BOOKINGS ||--o{ PAYMENTS : "dibayar"
    BOOKINGS ||--o{ CERTIFICATES : "menghasilkan"
```

---

## 3. Activity Diagram

### 3.1 Activity Diagram — Proses Booking Kursus

```mermaid
flowchart TD
    Start(("🟢 Mulai")) --> A["User membuka aplikasi"]
    A --> B["Login / Register via OTP"]
    B --> C{"Autentikasi berhasil?"}
    C -->|Tidak| B
    C -->|Ya| D["Melihat daftar kursus"]
    D --> E["Memilih kursus"]
    E --> F["Melihat detail kursus"]
    F --> G{"Ingin mendaftar?"}
    G -->|Tidak| D
    G -->|Ya| H["Memilih jadwal kursus"]
    H --> I["Mengisi data pribadi"]
    I --> J["Review ringkasan booking"]
    J --> K["Memilih metode pembayaran"]
    K --> L["Melakukan pembayaran"]
    L --> M{"Pembayaran berhasil?"}
    M -->|Gagal| N["Tampilkan pesan error"]
    N --> K
    M -->|Berhasil| O["Generate QR Code E-Ticket"]
    O --> P["Kirim notifikasi konfirmasi"]
    P --> Q["Status booking: PAID"]
    Q --> R(("🔴 Selesai"))
```

### 3.2 Activity Diagram — Proses Verifikasi LPK

```mermaid
flowchart TD
    Start(("🟢 Mulai")) --> A["LPK mendaftar ke sistem"]
    A --> B["Mengisi data & upload dokumen"]
    B --> C["Status: PENDING"]
    C --> D["Super Admin mereview"]
    D --> E{"Dokumen lengkap?"}
    E -->|Tidak| F["Minta kelengkapan dokumen"]
    F --> B
    E -->|Ya| G{"Memenuhi syarat?"}
    G -->|Tidak| H["Status: SUSPENDED"]
    H --> I(("🔴 Ditolak"))
    G -->|Ya| J["Catat verifikasi"]
    J --> K["Status: ACTIVE, is_verified = true"]
    K --> L["LPK dapat mengelola kursus"]
    L --> M(("🔴 Selesai"))
```

### 3.3 Activity Diagram — Proses Review Kursus

```mermaid
flowchart TD
    Start(("🟢 Mulai")) --> A["Peserta menyelesaikan kursus"]
    A --> B["Booking status: COMPLETED"]
    B --> C["Sistem menampilkan form review"]
    C --> D["Peserta memberikan rating 1-5"]
    D --> E["Menulis komentar"]
    E --> F{"Upload foto?"}
    F -->|Ya| G["Upload foto review"]
    F -->|Tidak| H["Submit review"]
    G --> H
    H --> I["is_verified_purchase = true"]
    I --> J["Review tampil di halaman kursus"]
    J --> K(("🔴 Selesai"))
```

---

## 4. Sequence Diagram

### 4.1 Sequence Diagram — Booking & Pembayaran

```mermaid
sequenceDiagram
    actor U as 👤 Peserta
    participant MA as 📱 Mobile App
    participant API as 🖥️ API Server
    participant DB as 🗄️ Database
    participant PG as 💳 Payment Gateway

    U->>MA: Pilih kursus & jadwal
    MA->>API: POST /api/v1/bookings
    API->>DB: Cek kapasitas jadwal
    DB-->>API: Slot tersedia

    API->>DB: INSERT booking (status: pending)
    DB-->>API: Booking created
    API->>API: Generate kode booking unik
    API-->>MA: Booking detail + kode

    U->>MA: Pilih metode pembayaran
    MA->>API: POST /api/v1/payments
    API->>PG: Create payment request
    PG-->>API: Virtual Account / QR Code

    API->>DB: INSERT payment (status: pending)
    API-->>MA: Detail pembayaran + VA Number

    U->>PG: Transfer / bayar
    PG->>API: Webhook callback (payment success)
    API->>DB: UPDATE payment (status: success)
    API->>DB: UPDATE booking (status: paid)
    API->>API: Generate QR Code E-Ticket
    API->>DB: UPDATE booking (qr_code)
    API->>MA: Push notification "Pembayaran berhasil"
    MA-->>U: Tampilkan E-Ticket dengan QR Code
```

### 4.2 Sequence Diagram — Login OTP

```mermaid
sequenceDiagram
    actor U as 👤 Peserta
    participant MA as 📱 Mobile App
    participant API as 🖥️ API Server
    participant DB as 🗄️ Database
    participant SMS as 📨 SMS Gateway

    U->>MA: Input nomor telepon
    MA->>API: POST /api/v1/auth/send-otp
    API->>DB: Cek user by phone
    alt User belum terdaftar
        API->>DB: INSERT user baru
    end
    API->>API: Generate OTP 6 digit
    API->>SMS: Kirim OTP via SMS
    SMS-->>U: SMS OTP diterima
    API-->>MA: OTP terkirim (countdown 60s)

    U->>MA: Input kode OTP
    MA->>API: POST /api/v1/auth/verify-otp
    API->>API: Validasi OTP
    alt OTP valid
        API->>DB: Generate personal access token
        API-->>MA: Token + user data
        MA-->>U: Redirect ke Home Screen
    else OTP invalid
        API-->>MA: Error "Kode OTP salah"
        MA-->>U: Tampilkan pesan error
    end
```

### 4.3 Sequence Diagram — Admin Mengelola Kursus

```mermaid
sequenceDiagram
    actor A as 👨‍💼 Admin LPK
    participant WEB as 🌐 Admin Panel
    participant SVR as 🖥️ Server
    participant DB as 🗄️ Database

    A->>WEB: Login admin
    WEB->>SVR: POST /admin/login
    SVR->>DB: Validasi kredensial
    DB-->>SVR: User valid (role: admin)
    SVR-->>WEB: Redirect ke dashboard

    A->>WEB: Buka halaman kursus
    WEB->>SVR: GET /admin/courses
    SVR->>DB: SELECT courses WHERE tenant_id = ?
    DB-->>SVR: Daftar kursus
    SVR-->>WEB: Render tabel kursus

    A->>WEB: Klik "Tambah Kursus"
    WEB->>SVR: GET /admin/courses/create
    SVR-->>WEB: Form input kursus

    A->>WEB: Submit data kursus + jadwal
    WEB->>SVR: POST /admin/courses
    SVR->>DB: INSERT course
    SVR->>DB: INSERT course_schedule
    DB-->>SVR: Kursus berhasil dibuat
    SVR-->>WEB: Redirect + success message
    WEB-->>A: "Kursus berhasil ditambahkan"
```

---

## 5. Flowchart

### 5.1 Flowchart — Alur Utama Sistem Skilloka

```mermaid
flowchart TD
    A["🟢 Start"] --> B["Buka Aplikasi Skilloka"]
    B --> C{"Sudah Login?"}
    C -->|Tidak| D["Tampilkan Onboarding"]
    D --> E["Login via OTP / Google"]
    E --> F{"Login Berhasil?"}
    F -->|Tidak| E
    F -->|Ya| G
    C -->|Ya| G["Tampilkan Home Screen"]

    G --> H{"Pilih Fitur"}

    H -->|Cari| I["Cari LPK / Kursus"]
    I --> J["Filter: Kategori, Lokasi, Harga"]
    J --> K["Tampilkan Hasil"]
    K --> L{"Pilih Item?"}
    L -->|LPK| M["Detail LPK"]
    L -->|Kursus| N["Detail Kursus"]

    H -->|Booking| N
    N --> O{"Daftar Kursus?"}
    O -->|Tidak| G
    O -->|Ya| P["Pilih Jadwal"]
    P --> Q["Isi Data Pendaftaran"]
    Q --> R["Pilih Pembayaran"]
    R --> S["Proses Pembayaran"]
    S --> T{"Berhasil?"}
    T -->|Tidak| R
    T -->|Ya| U["E-Ticket + QR Code"]

    H -->|Profil| V["Halaman Profil"]
    V --> W{"Aksi"}
    W -->|Booking| X["Riwayat Booking"]
    W -->|Sertifikat| Y["Daftar Sertifikat"]
    W -->|Review| Z["Tulis Review"]
    W -->|Logout| AA["Logout"]
    AA --> B

    U --> AB["🔴 End"]
    X --> AB
    Y --> AB
    Z --> AB
    M --> AB
```

### 5.2 Flowchart — Proses Pembayaran

```mermaid
flowchart TD
    A["Mulai Pembayaran"] --> B["Tampilkan Ringkasan"]
    B --> C["Pilih Metode Pembayaran"]
    C --> D{"Jenis Metode?"}

    D -->|VA Bank| E["Generate Virtual Account"]
    E --> F["Tampilkan Nomor VA + Instruksi"]
    F --> G["User Transfer via m-Banking"]

    D -->|E-Wallet| H["Generate QR / Deep Link"]
    H --> I["Redirect ke Aplikasi E-Wallet"]
    I --> G2["User Konfirmasi di E-Wallet"]

    G --> J["Menunggu Callback"]
    G2 --> J

    J --> K{"Status Callback?"}
    K -->|Timeout 24 jam| L["Booking Expired"]
    L --> M["Status: CANCELLED"]
    K -->|Gagal| N["Status: FAILED"]
    N --> O["Tampilkan Opsi Coba Lagi"]
    O --> C
    K -->|Sukses| P["Status: SUCCESS"]
    P --> Q["Update Booking: PAID"]
    Q --> R["Generate QR E-Ticket"]
    R --> S["Kirim Notifikasi"]
    S --> T["Selesai"]
```

---

## 6. Class Diagram

```mermaid
classDiagram
    class Tenant {
        +UUID id
        +String domain
        +String name
        +JSON settings
        +Boolean is_active
        +hasMany() users
        +hasMany() lpks
        +hasMany() courses
        +hasMany() bookings
    }

    class User {
        +BigInt id
        +UUID tenant_id
        +String name
        +String email
        +String phone
        +String avatar
        +BigInt location_id
        +String fcm_token
        +belongsTo() tenant
        +belongsTo() location
        +hasMany() bookings
        +hasMany() reviews
        +hasMany() certificates
    }

    class Location {
        +BigInt id
        +BigInt parent_id
        +String name
        +Enum level
        +String code
        +Decimal lat
        +Decimal long
        +parent() location
        +children() locations
        +hasMany() users
        +hasMany() lpks
    }

    class Lpk {
        +UUID id
        +UUID tenant_id
        +String name
        +String legal_name
        +String nib
        +BigInt location_id
        +String address
        +Decimal lat
        +Decimal long
        +Text description
        +JSON facilities
        +Boolean is_verified
        +Enum status
        +JSON contact_info
        +String logo
        +JSON images
        +belongsTo() tenant
        +belongsTo() location
        +hasMany() courses
        +hasMany() verifications
    }

    class LpkVerification {
        +BigInt id
        +UUID lpk_id
        +BigInt verified_by
        +Enum type
        +Text notes
        +JSON documents
        +Timestamp verified_at
        +belongsTo() lpk
        +belongsTo() verifier
    }

    class Category {
        +UUID id
        +String name
        +String slug
        +String icon
        +String color
        +Integer order
        +hasMany() courses
    }

    class Course {
        +UUID id
        +UUID tenant_id
        +UUID lpk_id
        +UUID category_id
        +String title
        +String slug
        +Text description
        +Text syllabus
        +Decimal price
        +Enum level
        +Integer duration_hours
        +String cert_type
        +JSON images
        +Boolean is_active
        +belongsTo() tenant
        +belongsTo() lpk
        +belongsTo() category
        +hasMany() schedules
        +hasMany() reviews
        +hasMany() certificates
    }

    class CourseSchedule {
        +UUID id
        +UUID course_id
        +Date start_date
        +Date end_date
        +Time daily_start
        +Time daily_end
        +Integer max_capacity
        +String days_of_week
        +Enum status
        +belongsTo() course
        +hasMany() bookings
        +availableSlots() int
    }

    class Booking {
        +UUID id
        +UUID tenant_id
        +BigInt user_id
        +UUID schedule_id
        +String code
        +Enum status
        +Decimal amount
        +String qr_code
        +Timestamp expires_at
        +belongsTo() tenant
        +belongsTo() user
        +belongsTo() schedule
        +hasOne() payment
        +hasOne() certificate
        +isPaid() bool
        +isExpired() bool
    }

    class Payment {
        +UUID id
        +UUID booking_id
        +String method
        +String provider
        +String external_id
        +Decimal amount
        +Enum status
        +Timestamp paid_at
        +JSON metadata
        +belongsTo() booking
        +isSuccess() bool
    }

    class Review {
        +BigInt id
        +BigInt user_id
        +UUID course_id
        +Integer rating
        +Text comment
        +JSON images
        +Boolean is_verified_purchase
        +belongsTo() user
        +belongsTo() course
    }

    class Certificate {
        +UUID id
        +BigInt user_id
        +UUID course_id
        +UUID booking_id
        +String number
        +String file_url
        +Date issue_date
        +Date expiry_date
        +belongsTo() user
        +belongsTo() course
        +belongsTo() booking
        +isValid() bool
    }

    Tenant "1" --> "*" User : memiliki
    Tenant "1" --> "*" Lpk : memiliki
    Tenant "1" --> "*" Course : memiliki
    Tenant "1" --> "*" Booking : memiliki

    Location "1" --> "*" Location : parent-child
    Location "1" --> "*" User : berlokasi
    Location "1" --> "*" Lpk : berlokasi

    Lpk "1" --> "*" Course : menyediakan
    Lpk "1" --> "*" LpkVerification : diverifikasi

    Category "1" --> "*" Course : mengkategorikan

    Course "1" --> "*" CourseSchedule : memiliki
    Course "1" --> "*" Review : menerima
    Course "1" --> "*" Certificate : menghasilkan

    CourseSchedule "1" --> "*" Booking : dibooking

    User "1" --> "*" Booking : melakukan
    User "1" --> "*" Review : menulis
    User "1" --> "*" Certificate : memiliki

    Booking "1" --> "0..1" Payment : dibayar
    Booking "1" --> "0..1" Certificate : menghasilkan
```

---

## Catatan Konvensi

| Simbol | Keterangan |
|--------|-----------|
| `PK` | Primary Key |
| `FK` | Foreign Key |
| `UK` | Unique Key |
| `UUID` | Universally Unique Identifier |
| `1 --> *` | One-to-Many relationship |
| `1 --> 0..1` | One-to-Zero-or-One relationship |
| `include` | Use case dependency |

> **Tools**: Diagram dibuat menggunakan Mermaid.js dan dapat di-render pada GitHub, VS Code (ekstensi Markdown Preview Mermaid), atau website [mermaid.live](https://mermaid.live).
