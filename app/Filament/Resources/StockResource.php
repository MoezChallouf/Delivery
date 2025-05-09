<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Filament\Resources\StockResource\RelationManagers;
use App\Models\Product;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  
    public static function table(Table $table): Table
    {
        return $table
        ->query(Product::query()->with(['stockMovements', 'packages']))
        ->columns([
            TextColumn::make('code_article')
                ->getStateUsing(fn (Product $record) => $record->code_article),
            TextColumn::make('current_stock')
            ->getStateUsing(function (Product $record) {
                $in = $record->stockMovements->where('type', 'IN')->sum('quantity');
                $out = $record->stockMovements->where('type', 'OUT')->sum('quantity');
                return $in - $out;
            }),
        ])
        ->query(Product::query()->with('stockMovements'))
        ->paginated(false) 
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStocks::route('/'),
        ];
    }    
}
