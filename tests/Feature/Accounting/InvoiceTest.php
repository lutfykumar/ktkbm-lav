<?php

namespace Tests\Feature\Accounting;

use App\Models\User;
use App\Models\Workorder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::find(2) ?? User::first();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/accounting/invoice');
        $response->assertRedirect('/login');
    }

    public function test_invoice_index_screen_can_be_rendered(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/accounting/invoice');

        $response->assertStatus(200);
        $response->assertSee('Total Unpaid');
        $response->assertSee('crudTable');
    }

    public function test_invoice_table_returns_json_array(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->postJson('/accounting/invoice/table');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    public function test_invoice_create_view_and_store_cycle(): void
    {
        $user = $this->getAdminUser();

        $wo = Workorder::first();
        if (!$wo) {
            $this->markTestSkipped('Tidak ada Work Order untuk pengetesan Invoice');
        }

        // Test create page
        $createRes = $this->actingAs($user)->get('/accounting/invoice/create/' . $wo->ID_WORK_ORDER);
        $createRes->assertStatus(200);
        $createRes->assertSee('Informasi Work Order');

        // Test store
        $storeData = [
            'id_work_order' => $wo->ID_WORK_ORDER,
            'tanggal_invoice' => date('Y-m-d'),
            'tanggal_jatuh_tempo' => date('Y-m-d', strtotime('+14 days')),
            'pajak_persen' => 11,
            'status' => 'draft',
            'catatan' => 'Testing invoice automated test',
            'deskripsi' => ['Jasa Bongkar Muat', 'Sewa Forklift'],
            'jumlah' => [100, 2],
            'satuan' => ['Ton', 'Unit'],
            'harga_satuan' => [50000, 250000],
        ];

        $storeRes = $this->actingAs($user)->postJson('/accounting/invoice/store', $storeData);
        $storeRes->assertStatus(200);
        $storeRes->assertJson(['status' => true]);

        $invoiceId = $storeRes->json('id');
        $this->assertNotNull($invoiceId);

        $invoice = Invoice::find($invoiceId);
        $this->assertNotNull($invoice);
        $this->assertStringStartsWith('INV-', $invoice->NO_INVOICE);

        // Test detail page
        $detailRes = $this->actingAs($user)->get('/accounting/invoice/detail/' . $invoiceId);
        $detailRes->assertStatus(200);
        $detailRes->assertSee($invoice->NO_INVOICE);

        // Test edit page
        $editRes = $this->actingAs($user)->get('/accounting/invoice/edit/' . $invoiceId);
        $editRes->assertStatus(200);
        $editRes->assertSee('Item Invoice');

        // Test print page
        $printRes = $this->actingAs($user)->get('/accounting/invoice/print_invoice/' . $invoiceId);
        $printRes->assertStatus(200);
        $printRes->assertSee('INVOICE');
        $printRes->assertSee($invoice->NO_INVOICE);

        // Test update
        $updateRes = $this->actingAs($user)->postJson('/accounting/invoice/update', [
            'id_invoice' => $invoiceId,
            'tanggal_invoice' => date('Y-m-d'),
            'tanggal_jatuh_tempo' => date('Y-m-d', strtotime('+30 days')),
            'pajak_persen' => 11,
            'status' => 'sent',
            'catatan' => 'Updated invoice test',
            'deskripsi' => ['Jasa Bongkar Muat updated'],
            'jumlah' => [120],
            'satuan' => ['Ton'],
            'harga_satuan' => [55000],
        ]);
        $updateRes->assertStatus(200);
        $updateRes->assertJson(['status' => true]);

        // Test update payment
        $paymentRes = $this->actingAs($user)->postJson('/accounting/invoice/update_payment', [
            'id_invoice' => $invoiceId,
            'tanggal_bayar' => date('Y-m-d'),
            'metode_bayar' => 'Transfer Bank',
        ]);
        $paymentRes->assertStatus(200);
        $paymentRes->assertJson(['status' => true]);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->STATUS);

        // Clean up test invoice & items directly (since status is paid, soft-delete directly in DB)
        InvoiceItem::where('ID_INVOICE', $invoiceId)->forceDelete();
        $invoice->forceDelete();
    }
}
