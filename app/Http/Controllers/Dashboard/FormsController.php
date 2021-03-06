<?php

/**
 * NexoPOS Controller
 * @since  1.0
**/

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\DashboardController;
use App\Http\Requests\FormsRequest;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


use Tendoo\Core\Exceptions\CoreException;

use App\Models\ProductCategory;
use App\Models\User;
use App\Services\SettingsPage;
use Exception;
use TorMorten\Eventy\Facades\Events as Hook;

class FormsController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getForm( $identifier ) 
    {
        /**
         * @var SettingsPage
         */
        $instance   =   Hook::filter( 'ns.forms', [], $identifier );

        if ( ! $instance instanceof SettingsPage ) {
            throw new Exception( sprintf( 
                '%s is not an instanceof "%s".',
                $identifier, 
                SettingsPage::class 
            ) );
        }

        return $instance->getForm();
    }

    public function saveForm( FormsRequest $request, $identifier )
    {
        $instance   =   Hook::filter( 'ns.forms', [], $identifier );

        if ( ! $instance instanceof SettingsPage ) {
            throw new Exception( sprintf( 
                '%s is not an instanceof "%s".',
                $identifier, 
                SettingsPage::class 
            ) );
        }

        return $instance->saveForm( $request );
    }
}

