<?php

namespace App\Filament\Pages;

use App\Models\Site;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MailSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Mail Settings';
    protected static ?string $title = 'Mail Settings';
    protected static string $view = 'filament.pages.mail-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->loadSite(Site::orderBy('id')->value('id'));
    }

    protected function loadSite($siteId): void
    {
        $site = $siteId ? Site::find($siteId) : null;

        $this->form->fill([
            'site_id' => $site?->id,
            'mail_host' => $site?->mail_host,
            'mail_port' => $site?->mail_port,
            'mail_username' => $site?->mail_username,
            // Never send the stored password back to the browser
            'mail_password' => null,
            'mail_encryption' => $site?->mail_encryption ?? 'tls',
            'mail_from_address' => $site?->mail_from_address,
            'mail_from_name' => $site?->mail_from_name,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Select::make('site_id')
                    ->label('Site')
                    ->options(Site::pluck('name', 'id'))
                    ->required()
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->loadSite($state)),

                Section::make('SMTP Account')
                    ->description('Used to send order emails for the selected site. Leave empty to use the default server mail settings.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('mail_host')
                            ->label('SMTP Host')
                            ->placeholder('smtp.gmail.com')
                            ->maxLength(255),

                        TextInput::make('mail_port')
                            ->label('SMTP Port')
                            ->numeric()
                            ->placeholder('587'),

                        TextInput::make('mail_username')
                            ->label('SMTP Username')
                            ->placeholder('you@gmail.com')
                            ->maxLength(255)
                            ->requiredWith('mail_host'),

                        TextInput::make('mail_password')
                            ->label('SMTP Password')
                            ->password()
                            ->revealable()
                            // Stop the browser autofilling the admin login password here
                            ->autocomplete('new-password')
                            ->placeholder(fn() => $this->currentSite()?->mail_password ? '•••••••• (saved)' : null)
                            ->helperText('For Gmail, use an App Password. Leave blank to keep the current password.'),

                        Select::make('mail_encryption')
                            ->label('Encryption')
                            ->options(['tls' => 'TLS (port 587)', 'ssl' => 'SSL (port 465)']),

                        TextInput::make('mail_from_address')
                            ->label('From Address')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Defaults to the SMTP username. Gmail only allows your own address or a verified alias.'),

                        TextInput::make('mail_from_name')
                            ->label('From Name')
                            ->maxLength(255)
                            ->helperText('Defaults to the site name.'),
                    ]),
            ]);
    }

    protected function currentSite(): ?Site
    {
        return filled($this->data['site_id'] ?? null) ? Site::find($this->data['site_id']) : null;
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $site = Site::findOrFail($state['site_id']);

        $values = collect($state)->except(['site_id', 'mail_password'])->all();

        if (filled($state['mail_password'])) {
            $values['mail_password'] = $state['mail_password'];
        }

        $site->update($values);
        $this->data['mail_password'] = null;

        Notification::make()->title("Mail settings saved for {$site->name}")->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTest')
                ->label('Send Test Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->form([
                    TextInput::make('to')
                        ->label('Send to')
                        ->email()
                        ->required()
                        ->default(fn() => $this->currentSite()?->admin_email),
                ])
                ->modalDescription('Uses the saved settings for the selected site. Save your changes first.')
                ->action(function (array $data) {
                    $site = $this->currentSite();

                    if (!$site) {
                        return;
                    }

                    try {
                        $site->mailer()->raw(
                            "This is a test email from {$site->name}. Your mail settings are working.",
                            fn($message) => $message->to($data['to'])->subject("Test email — {$site->name}")
                        );

                        Notification::make()->title('Test email sent to ' . $data['to'])->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Test email failed')->body($e->getMessage())->danger()->persistent()->send();
                    }
                }),
        ];
    }
}
