<?php

namespace Database\Seeders;

use App\Models\ApiEndpoint;
use App\Models\Document;
use App\Models\Folder;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::exists() || Project::exists()) {
            return;
        }

        // 1. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@devdocs.local'],
            [
                'name' => 'Admin Developer',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Demo Project 1: Payment Center
        $paymentCenter = Project::firstOrCreate(
            ['slug' => 'payment-center'],
            [
                'owner_id' => $admin->id,
                'name' => 'Payment Center',
                'description' => 'Dokumentasi arsitektur, gateway pembayaran QRIS, Virtual Account, dan webhook callback.',
                'icon' => 'credit-card',
                'status' => 'active',
            ]
        );

        // 3. Demo Project 2: Kawitan Core
        Project::firstOrCreate(
            ['slug' => 'kawitan-core'],
            [
                'owner_id' => $admin->id,
                'name' => 'Kawitan Core Services',
                'description' => 'Infrastruktur backend microservices, authentication service, dan event streaming.',
                'icon' => 'server',
                'status' => 'active',
            ]
        );

        // 4. Folders for Payment Center
        $fArch = Folder::firstOrCreate(
            ['project_id' => $paymentCenter->id, 'name' => 'Architecture'],
            ['position' => 1]
        );

        $fSeq = Folder::firstOrCreate(
            ['project_id' => $paymentCenter->id, 'name' => 'Sequence Diagrams', 'parent_id' => $fArch->id],
            ['position' => 1]
        );

        $fApi = Folder::firstOrCreate(
            ['project_id' => $paymentCenter->id, 'name' => 'API Documentation'],
            ['position' => 2]
        );

        $fDb = Folder::firstOrCreate(
            ['project_id' => $paymentCenter->id, 'name' => 'Database Schema'],
            ['position' => 3]
        );

        // 5. Sample Document: QRIS MPM Callback
        $qrisDocContent = <<<MARKDOWN
# QRIS MPM Payment Notification Callback

## Ringkasan Alur
Dokumentasi ini menjelaskan spesifikasi teknis untuk menerima notifikasi pembayaran QRIS (Merchant Presented Mode) dari pihak Switching / Aggregator ke Payment Center internal.

> [!IMPORTANT]
> Semua request callback wajib diverifikasi tanda tangan digitalnya (**X-SIGNATURE**) menggunakan HMAC-SHA256 sebelum memproses status transaksi di database.

---

## Sequence Diagram

```mermaid
sequenceDiagram
    autonumber
    actor Customer as Pelanggan
    participant Gateway as Payment Gateway
    participant API as DevDocs API
    participant Worker as Payment Service
    participant DB as PostgreSQL DB

    Customer->>Gateway: Scan & Bayar QRIS
    Gateway->>API: POST /api/v1.0/qr/qr-mpm-notify
    API->>API: Validasi Header & X-SIGNATURE
    alt Signature Valid
        API->>Worker: Dispatch Job (ProcessTransaction)
        Worker->>DB: UPDATE transactions SET status='SUCCESS'
        DB-->>Worker: Commit
        API-->>Gateway: HTTP 200 (Success Callback)
    else Signature Invalid
        API-->>Gateway: HTTP 401 Unauthorized (Invalid Signature)
    end
```

---

## Spesifikasi Endpoint

- **Method**: `POST`
- **Path**: `/api/v1.0/qr/qr-mpm-notify`
- **Content-Type**: `application/json`

### Header Request

| Header | Wajib | Tipe | Contoh / Keterangan |
| :--- | :---: | :--- | :--- |
| `Authorization` | Ya | String | `Bearer <token>` |
| `X-TIMESTAMP` | Ya | String | `2026-10-29T15:30:00+07:00` |
| `X-SIGNATURE` | Ya | String | Hex HMAC-SHA256 hash |
| `X-PARTNER-ID` | Ya | String | ID Mitra terdaftar |

### Contoh Request Body

```json
{
  "partnerReferenceNo": "INV-20261029-0012",
  "amount": {
    "value": "75000.00",
    "currency": "IDR"
  },
  "additionalInfo": {
    "terminalId": "A01928",
    "qrType": "STATIK"
  }
}
```

### Contoh cURL Command

```bash
curl -X POST "http://192.168.1.100/api/v1.0/qr/qr-mpm-notify" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer <TOKEN>" \
     -H "X-TIMESTAMP: 2026-10-29T15:30:00+07:00" \
     -H "X-SIGNATURE: 4a3f8902bc1298ef9120ac4" \
     -d '{
       "partnerReferenceNo": "INV-20261029-0012",
       "amount": { "value": "75000.00", "currency": "IDR" }
     }'
```

### Response Status

| HTTP Code | Status Code | Deskripsi |
| :--- | :--- | :--- |
| `200 OK` | `2002500` | Transaksi berhasil diterima dan diproses |
| `400 Bad Request` | `4002501` | Format payload tidak sesuai schema |
| `401 Unauthorized` | `4012500` | Tanda tangan digital (*X-SIGNATURE*) tidak valid |
MARKDOWN;

        $doc = Document::firstOrCreate(
            ['project_id' => $paymentCenter->id, 'slug' => 'qris-mpm-callback'],
            [
                'folder_id' => $fSeq->id,
                'author_id' => $admin->id,
                'last_editor_id' => $admin->id,
                'title' => 'QRIS MPM Payment Notification Callback',
                'summary' => 'Dokumentasi lengkap webhook notifikasi transaksi QRIS Merchant Presented Mode.',
                'content_markdown' => $qrisDocContent,
                'status' => 'published',
                'version' => 1,
                'published_at' => now(),
            ]
        );

        // 6. Linked API Endpoint Record
        ApiEndpoint::firstOrCreate(
            ['document_id' => $doc->id, 'path' => '/api/v1.0/qr/qr-mpm-notify'],
            [
                'name' => 'QRIS MPM Notification Webhook',
                'method' => 'POST',
                'description' => 'Menerima callback real-time saat pelanggan berhasil membayar melalui QRIS MPM.',
                'authentication' => 'HMAC-SHA256 & Bearer',
                'headers' => [
                    ['key' => 'Authorization', 'value' => 'Bearer <token>', 'required' => true],
                    ['key' => 'X-TIMESTAMP', 'value' => 'ISO8601 Timestamp', 'required' => true],
                    ['key' => 'X-SIGNATURE', 'value' => 'HMAC-SHA256 Hex', 'required' => true],
                    ['key' => 'X-PARTNER-ID', 'value' => 'Partner ID Mitra', 'required' => true],
                ],
                'parameters' => [],
                'request_body' => [
                    'partnerReferenceNo' => 'INV-20261029-0012',
                    'amount' => ['value' => '75000.00', 'currency' => 'IDR'],
                ],
                'responses' => [
                    ['status' => 200, 'code' => '2002500', 'message' => 'Successful'],
                    ['status' => 401, 'code' => '4012500', 'message' => 'Unauthorized Signature'],
                ],
            ]
        );

        // 7. Sample Document 2: Architecture Overview
        Document::firstOrCreate(
            ['project_id' => $paymentCenter->id, 'slug' => 'overview-arsitektur'],
            [
                'folder_id' => $fArch->id,
                'author_id' => $admin->id,
                'last_editor_id' => $admin->id,
                'title' => 'Overview Arsitektur Sistem',
                'summary' => 'Diagram blok high-level Payment Center dan integrasi antarmuka perbankan.',
                'content_markdown' => "# Overview Arsitektur Payment Center\n\nPayment Center bertindak sebagai penghubung antara aplikasi internal (E-Commerce, POS, Mobile App) dengan berbagai penyedia pembayaran eksternal.\n\n```mermaid\ngraph TD\n    App[Aplikasi Klien] -->|REST API| Gateway[API Gateway]\n    Gateway --> Core[Payment Core Engine]\n    Core --> DB[(PostgreSQL Cluster)]\n    Core --> Queue[(Redis Queue)]\n    Queue --> Worker[Background Worker]\n    Worker --> Bank[Bank & QRIS Aggregator]\n```\n\n## Komponen Utama\n1. **API Gateway**: Menangani rate limiting, autentikasi client, dan TLS termination.\n2. **Payment Core**: Business logic validasi transaksi, pembuatan tagihan, dan rekonsiliasi.\n3. **PostgreSQL**: Sumber kebenaran data transaksi, audit trail, dan log perubahan.",
                'status' => 'published',
                'version' => 1,
                'published_at' => now(),
            ]
        );
    }
}
