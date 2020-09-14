<?php
namespace App\Crud;

use App\Exceptions\NotAllowedException;
use App\Models\ProductHistory;
use App\Services\CrudService;
use App\Services\Users;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use TorMorten\Eventy\Facades\Events as Hook;
use Exception;

class ProductHistoryCrud extends CrudService
{
    /**
     * define the base table
     */
    protected $table      =   'nexopos_products_histories';

    /**
     * default identifier
     */
    protected $identifier   =   'products/histories';

    /**
     * Define namespace
     * @param  string
     */
    protected $namespace  =   'ns.products-histories';

    /**
     * Model Used
     */
    protected $model      =   ProductHistory::class;

    /**
     * Define permissions
     * @param  array
     */
    protected $permissions  =   [
        'create'    =>  false,
        'read'      =>  'manage.profile',
        'update'    =>  false,
        'delete'    =>  false,
    ];

    /**
     * Adding relation
     */
    public $relations   =  [
        [ 'nexopos_products as products', 'nexopos_products_histories.product_id', '=', 'products.id' ],
        [ 'nexopos_users as users', 'nexopos_products_histories.author', '=', 'users.id' ],
        [ 'nexopos_units as units', 'nexopos_products_histories.unit_id', '=', 'units.id' ],
        'leftJoin'  =>  [
            [ 'nexopos_procurements as procurements', 'nexopos_products_histories.procurement_id', '=', 'procurements.id' ],
            [ 'nexopos_orders as orders', 'nexopos_products_histories.order_id', '=', 'orders.id' ],
        ]
    ];

    /**
     * Pick
     * Restrict columns you retreive from relation.
     * Should be an array of associative keys, where 
     * keys are either the related table or alias name.
     * Example : [
     *      'user'  =>  [ 'username' ], // here the relation on the table nexopos_users is using "user" as an alias
     * ]
     */
    public $pick        =   [
        'users'         =>  [ 'username' ],
        'units'         =>  [ 'name' ],
        'procurements'  =>  [ 'name' ],
        'orders'        =>  [ 'code' ],
        'products'      =>  [ 'name' ],
    ];

    /**
     * Define where statement
     * @var  array
    **/
    protected $listWhere    =   [];

    /**
     * Define where in statement
     * @var  array
     */
    protected $whereIn      =   [];

    /**
     * Fields which will be filled during post/put
     */
        public $fillable    =   [];

    /**
     * Define Constructor
     * @param  
     */
    public function __construct()
    {
        parent::__construct();

        Hook::addFilter( $this->namespace . '-crud-actions', [ $this, 'setActions' ], 10, 2 );
    }

    /**
     * Return the label used for the crud 
     * instance
     * @return  array
    **/
    public function getLabels()
    {
        return [
            'list_title'            =>  __( 'Product Histories' ),
            'list_description'      =>  __( 'Display all product histories.' ),
            'no_entry'              =>  __( 'No product histories has been registered' ),
            'create_new'            =>  __( 'Add a new product history' ),
            'create_title'          =>  __( 'Create a new product history' ),
            'create_description'    =>  __( 'Register a new product history and save it.' ),
            'edit_title'            =>  __( 'Edit product history' ),
            'edit_description'      =>  __( 'Modify  Product History.' ),
            'back_to_list'          =>  __( 'Return to Product Histories' ),
        ];
    }

    /**
     * Check whether a feature is enabled
     * @return  boolean
    **/
    public function isEnabled( $feature )
    {
        return false; // by default
    }

    /**
     * Fields
     * @param  object/null
     * @return  array of field
     */
    public function getForm( $entry = null ) 
    {
        return [
            'main' =>  [
                'label'         =>  __( 'Name' ),
                // 'name'          =>  'name',
                // 'value'         =>  $entry->name ?? '',
                'description'   =>  __( 'Provide a name to the resource.' )
            ],
            'tabs'  =>  [
                'general'   =>  [
                    'label'     =>  __( 'General' ),
                    'fields'    =>  [
                        [
                            'type'  =>  'text',
                            'name'  =>  'after_quantity',
                            'label' =>  __( 'After_quantity' ),
                            'value' =>  $entry->after_quantity ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'author',
                            'label' =>  __( 'Author' ),
                            'value' =>  $entry->author ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'before_quantity',
                            'label' =>  __( 'Before_quantity' ),
                            'value' =>  $entry->before_quantity ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'created_at',
                            'label' =>  __( 'Created_at' ),
                            'value' =>  $entry->created_at ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'id',
                            'label' =>  __( 'Id' ),
                            'value' =>  $entry->id ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'operation_type',
                            'label' =>  __( 'Operation_type' ),
                            'value' =>  $entry->operation_type ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'order_id',
                            'label' =>  __( 'Order_id' ),
                            'value' =>  $entry->order_id ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'procurement_id',
                            'label' =>  __( 'Procurement_id' ),
                            'value' =>  $entry->procurement_id ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'procurement_product_id',
                            'label' =>  __( 'Procurement_product_id' ),
                            'value' =>  $entry->procurement_product_id ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'product_id',
                            'label' =>  __( 'Product_id' ),
                            'value' =>  $entry->product_id ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'quantity',
                            'label' =>  __( 'Quantity' ),
                            'value' =>  $entry->quantity ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'total_price',
                            'label' =>  __( 'Total_price' ),
                            'value' =>  $entry->total_price ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'unit_id',
                            'label' =>  __( 'Unit_id' ),
                            'value' =>  $entry->unit_id ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'unit_price',
                            'label' =>  __( 'Unit_price' ),
                            'value' =>  $entry->unit_price ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'updated_at',
                            'label' =>  __( 'Updated_at' ),
                            'value' =>  $entry->updated_at ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'uuid',
                            'label' =>  __( 'Uuid' ),
                            'value' =>  $entry->uuid ?? '',
                        ],                     ]
                ]
            ]
        ];
    }

    /**
     * Filter POST input fields
     * @param  array of fields
     * @return  array of fields
     */
    public function filterPostInputs( $inputs )
    {
        return $inputs;
    }

    /**
     * Filter PUT input fields
     * @param  array of fields
     * @return  array of fields
     */
    public function filterPutInputs( $inputs, ProductHistory $entry )
    {
        return $inputs;
    }

    /**
     * Before saving a record
     * @param  Request $request
     * @return  void
     */
    public function beforePost( $request )
    {
        if ( $this->permissions[ 'create' ] !== false ) {
            ns()->restrict( $this->permissions[ 'create' ] );
        } else {
            throw new NotAllowedException();
        }

        return $request;
    }

    /**
     * After saving a record
     * @param  Request $request
     * @param  ProductHistory $entry
     * @return  void
     */
    public function afterPost( $request, ProductHistory $entry )
    {
        return $request;
    }

    
    /**
     * get
     * @param  string
     * @return  mixed
     */
    public function get( $param )
    {
        switch( $param ) {
            case 'model' : return $this->model ; break;
        }
    }

    /**
     * Before updating a record
     * @param  Request $request
     * @param  object entry
     * @return  void
     */
    public function beforePut( $request, $entry )
    {
        if ( $this->permissions[ 'update' ] !== false ) {
            ns()->restrict( $this->permissions[ 'update' ] );
        } else {
            throw new NotAllowedException;
        }

        return $request;
    }

    /**
     * After updating a record
     * @param  Request $request
     * @param  object entry
     * @return  void
     */
    public function afterPut( $request, $entry )
    {
        return $request;
    }

    /**
     * Before Delete
     * @return  void
     */
    public function beforeDelete( $namespace, $id, $model ) {
        if ( $namespace == 'ns.products-histories' ) {
            /**
             *  Perform an action before deleting an entry
             *  In case something wrong, this response can be returned
             *
             *  return response([
             *      'status'    =>  'danger',
             *      'message'   =>  __( 'You\re not allowed to do that.' )
             *  ], 403 );
            **/
            if ( $this->permissions[ 'delete' ] !== false ) {
                ns()->restrict( $this->permissions[ 'delete' ] );
            } else {
                throw new NotAllowedException;
            }
        }
    }

    /**
     * Define Columns
     * @return  array of columns configuration
     */
    public function getColumns() {
        return [
            'products_name'  =>  [
                'label'  =>  __( 'Product' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'operation_type'  =>  [
                'label'  =>  __( 'Operation' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'before_quantity'  =>  [
                'label'  =>  __( 'P. Quantity' ),
                'width'         =>  '150px',
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'quantity'  =>  [
                'label'  =>  __( 'Quantity' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'after_quantity'  =>  [
                'label'         =>  __( 'N. Quantity' ),
                '$direction'    =>  '',
                'width'         =>  '150px',
                '$sort'         =>  false
            ],
            'units_name'  =>  [
                'label'         =>  __( 'Unit' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'orders_code'  =>  [
                'label'         =>  __( 'Order' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'procurements_name'  =>  [
                'label'         =>  __( 'Procurement' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'unit_price'  =>  [
                'label'         =>  __( 'Unit Price' ),
                '$direction'    =>  '',
                'width'         =>  '150px',
                '$sort'         =>  false
            ],
            'total_price'  =>  [
                'label'         =>  __( 'Total Price' ),
                '$direction'    =>  '',
                'width'         =>  '150px',
                '$sort'         =>  false
            ],
            'users_username'  =>  [
                'label'         =>  __( 'Author' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'created_at'  =>  [
                'label'         =>  __( 'Date' ),
                'width'         =>  '150px',
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
        ];
    }

    /**
     * Define actions
     */
    public function setActions( $entry, $namespace )
    {
        // Don't overwrite
        $entry->{ '$checked' }      =   false;
        $entry->{ '$toggled' }      =   false;
        $entry->{ '$id' }           =   $entry->id;

        $entry->orders_code         =   $entry->orders_code ?: __( 'N/A' );
        $entry->procurements_name   =   $entry->procurements_name ?: __( 'N/A' );
        $entry->unit_price          =   ns()->currency->define( $entry->unit_price )
            ->format();

        $entry->total_price         =   ns()->currency->define( $entry->total_price )
            ->format();

        // you can make changes here
        $entry->{'$actions'}    =   [
            
        ];

        return $entry;
    }

    
    /**
     * Bulk Delete Action
     * @param    object Request with object
     * @return    false/array
     */
    public function bulkAction( Request $request ) 
    {
        /**
         * Deleting licence is only allowed for admin
         * and supervisor.
         */

        if ( $request->input( 'action' ) == 'delete_selected' ) {

            /**
             * Will control if the user has the permissoin to do that.
             */
            if ( $this->permissions[ 'delete' ] !== false ) {
                ns()->restrict( $this->permissions[ 'delete' ] );
            } else {
                throw new NotAllowedException;
            }

            $status     =   [
                'success'   =>  0,
                'failed'    =>  0
            ];

            foreach ( $request->input( 'entries' ) as $id ) {
                $entity     =   $this->model::find( $id );
                if ( $entity instanceof ProductHistory ) {
                    $entity->delete();
                    $status[ 'success' ]++;
                } else {
                    $status[ 'failed' ]++;
                }
            }
            return $status;
        }

        return Hook::filter( $this->namespace . '-catch-action', false, $request );
    }

    public function hook( $query )
    {
        $query->orderBy( 'id', 'desc' );
    }

    /**
     * get Links
     * @return  array of links
     */
    public function getLinks()
    {
        return  [
            'list'      =>  url( 'dashboard/' . 'products/histories' ),
            'create'    =>  false,
            'edit'      =>  false,
            'post'      =>  false,
            'put'       =>  false,
        ];
    }

    /**
     * Get Bulk actions
     * @return  array of actions
    **/
    public function getBulkActions()
    {
        return Hook::filter( $this->namespace . '-bulk', [
            [
                'label'         =>  __( 'Delete Selected Groups' ),
                'identifier'    =>  'delete_selected',
                'url'           =>  route( 'crud.bulk-actions', [
                    'namespace' =>  $this->namespace
                ])
            ]
        ]);
    }

    /**
     * get exports
     * @return  array of export formats
    **/
    public function getExports()
    {
        return [];
    }
}