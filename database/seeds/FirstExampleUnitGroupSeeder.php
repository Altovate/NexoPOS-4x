<?php

use Illuminate\Database\Seeder;
use App\Models\UnitGroup;
use App\Models\Unit;
use App\Models\User;

class FirstExampleUnitGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $author             =   User::get()->map( fn( $user ) => $user->id )
            ->shuffle()
            ->first();

        $unitGroup          =   new UnitGroup;
        $unitGroup->name    =   'Countable';
        $unitGroup->author  =   $author;
        $unitGroup->save();

        $unit               =   new Unit;
        $unit->group_id     =   $unitGroup->id;
        $unit->value        =   1;
        $unit->base_unit    =   true;
        $unit->name         =   'Piece';
        $unit->author       =   $author;
        $unit->save();

        $unit               =   new Unit;
        $unit->group_id     =   $unitGroup->id;
        $unit->value        =   10;
        $unit->base_unit    =   false;
        $unit->name         =   'Decade';
        $unit->author       =   $author;
        $unit->save();

        $unit               =   new Unit;
        $unit->group_id     =   $unitGroup->id;
        $unit->value        =   50;
        $unit->base_unit    =   false;
        $unit->name         =   'Fifties';
        $unit->author       =   $author;
        $unit->save();

        $unitGroup          =   new UnitGroup;
        $unitGroup->name    =   'Wine (Yalumba)';
        $unitGroup->author  =   $author;
        $unitGroup->save();

        $unit               =   new Unit;
        $unit->group_id     =   $unitGroup->id;
        $unit->value        =   300;
        $unit->base_unit    =   true;
        $unit->name         =   'Shot (200ml)';
        $unit->author       =   $author;
        $unit->save();

        $unit               =   new Unit;
        $unit->group_id     =   $unitGroup->id;
        $unit->value        =   600;
        $unit->base_unit    =   false;
        $unit->name         =   'Half Bottle (600ml)';
        $unit->author       =   $author;
        $unit->save();

        $unit               =   new Unit;
        $unit->group_id     =   $unitGroup->id;
        $unit->value        =   1200;
        $unit->base_unit    =   false;
        $unit->name         =   'Bottle (1200ml)';
        $unit->author       =   $author;
        $unit->save();

        $unit               =   new Unit;
        $unit->group_id     =   $unitGroup->id;
        $unit->value        =   1200*6;
        $unit->base_unit    =   false;
        $unit->name         =   '6 Bottles Box (1200mlx6)';
        $unit->author       =   $author;
        $unit->save();
    }
}
