<?php

namespace App\Filament\Resources;
use SimpleSoftwareIO\QrCode\Facades\QrCode; 

use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Select::make('product_id')
                ->relationship('product', 'code_article')
                ->required(),
            TextInput::make('quantity')
                ->numeric()
                ->required(),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('product.code_article'),
            TextColumn::make('quantity'),
            TextColumn::make('status'),
            TextColumn::make('qr_code'),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('confirm')
                ->action(fn (Package $record) => $record->update(['status' => 'Confirmed']))
                ->visible(fn ($record) => $record->status === 'Draft'),
                
                Action::make('sticker')
                ->label('Generate Sticker')
                ->icon('heroicon-o-qr-code')
                ->color('success')
                ->modalHeading('Package Sticker')
                ->modalContent(fn (Package $record) => view(
                    'filament.package-sticker',
                    [
                        'qrCode' => QrCode::size(pixels: 70)->generate($record->qr_code), // Now valid
                        'package' => $record
                    ]
                ))
                ->modalFooterActions([
                    Action::make('print')
                    ->label('Print Now')
                    ->color('primary')
                    ->icon('heroicon-o-printer')
                    ->extraAttributes(['onclick' => 'window.print()'])
                ])
                ->modalWidth('3xl'),
                ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }    
}
