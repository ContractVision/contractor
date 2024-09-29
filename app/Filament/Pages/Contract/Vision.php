<?php

namespace App\Filament\Pages\Contract;

use App\Jobs\ParseContracts;
use App\Models\Contract;
use App\Services\GenerateService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Vision extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.contract.vision';

    use WithFileUploads;

    public $photos = [];

    public function save()
    {
        $this->validate([
            'photos.*' => 'file|max:2048', // 1MB Max
        ]);

        foreach ($this->photos as $photo) {

            $contract = new Contract();
            $contract->company_id = auth()->user()->company_id;
            $contract->save();

            /* @var Media $media */
            $contract->addMedia($photo)->toMediaCollection('default', 'local');

//            ParseContracts::dispatch($contract);

            $result = json_decode('{
   "companies": {
      "from": {
         "id": 1,
         "name": "Service Provider Inc.",
         "entity_type": "Corporation",
         "email": "contact@serviceprovider.com",
         "phone": "+1234567890",
         "address": "123 Service St, Business City, BC"
      },
      "to": {
         "id": 2,
         "name": "Client Corp.",
         "entity_type": "Corporation",
         "email": "info@clientcorp.com",
         "phone": "+0987654321",
         "address": "456 Client Ave, Client Town, CT"
      }
   },
   "contract": {
      "id": 1,
      "from_company_id": 1,
      "to_company_id": 2,
      "currency": "USD",
      "language_code": "EN",
      "contract_date": "2024-09-29",
      "contract_start_date": "2024-10-01",
      "contract_due_date": "2025-10-01",
      "total": 1500.00,
      "limited": false
   },
   "services": [
      {
         "id": 1,
         "contract_id": 1,
         "description": "Web Development Services",
         "quantity": 1,
         "price": 1500.00
      }
   ],
   "bank_details": {
      "company_id": 1,
      "bank_details": "Bank of Services, Account No: 123456789, Sort Code: 12-34-56"
   },
   "notes": [
      {
         "id": 1,
         "contract_id": 1,
         "note": "Payment due within 30 days of invoice."
      }
   ],
   "invoices": [
      {
         "CreatedAt": "2024-09-29",
         "dueDate": "2025-10-01",
         "productDescription": "Web Development Services",
         "productQuantity": 1,
         "productPrice": 1500.00,
         "totalPrice": 1500.00
      }
   ]
}
', true);

            (new GenerateService())->generateFromResponse($contract, $result);

        }

        Notification::make()
            ->title('Contract has been uploaded.')
            ->send();
    }

}
