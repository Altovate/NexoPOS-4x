<?php
namespace App\Crud;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\CrudService;
use App\Services\Helper;
use App\Models\User;
use TorMorten\Eventy\Facades\Events as Hook;
use Exception;
use App\Models\Coupon;
use App\Models\CouponProduct;
use App\Models\CouponCategory;
use App\Models\Product;
use App\Models\ProductCategory;

class CouponCrud extends CrudService
{
    /**
     * define the base table
     */
    protected $table      =   'nexopos_customers_coupons';

    /**
     * base route name
     */
    protected $mainRoute      =   'ns.coupons';

    /**
     * Define namespace
     * @param  string
     */
    protected $namespace  =   'ns.coupons';

    /**
     * Model Used
     */
    protected $model      =   Coupon::class;

    /**
     * Adding relation
     */
    public $relations   =  [
        [ 'nexopos_users', 'nexopos_customers_coupons.author', '=', 'nexopos_users.id' ]
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
            'list_title'            =>  __( 'Customer Coupons List' ),
            'list_description'      =>  __( 'Display all customer coupons.' ),
            'no_entry'              =>  __( 'No customer coupons has been registered' ),
            'create_new'            =>  __( 'Add a new customer coupon' ),
            'create_title'          =>  __( 'Create a new customer coupon' ),
            'create_description'    =>  __( 'Register a new customer coupon and save it.' ),
            'edit_title'            =>  __( 'Edit customer coupon' ),
            'edit_description'      =>  __( 'Modify  Customer Coupon.' ),
            'back_to_list'          =>  __( 'Return to Customer Coupons' ),
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
                'name'          =>  'name',
                'value'         =>  $entry->name ?? '',
                'description'   =>  __( 'Provide a name to the resource.' )
            ],
            'tabs'  =>  [
                'general'   =>  [
                    'label'     =>  __( 'General' ),
                    'active'    =>  false,
                    'fields'    =>  [
                        [
                            'type'  =>  'select',
                            'name'  =>  'type',
                            'options'   =>  Helper::kvToJsOptions([
                                'percentage_discount'   =>  __( 'Percentage Discount' ),
                                'flat_discount'         =>  __( 'Flat Discount' ),
                            ]),
                            'label' =>  __( 'Type' ),
                            'value' =>  $entry->type ?? '',
                            'description'   =>  __( 'Define which type of discount apply to the current coupon.' )
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'discount_value',
                            'label' =>  __( 'Discount Value' ),
                            'description'   =>  __( 'Define the percentage or flat value.' ),
                            'value' =>  $entry->discount_value ?? '',
                        ], [
                            'type'  =>  'datetime',
                            'name'  =>  'valid_until',
                            'label' =>  __( 'Valid Until' ),
                            'description'   =>  __( 'Determin Until When the coupon is valid.' ),
                            'value' =>  $entry->valid_until ?? '',
                        ], [
                            'type'  =>  'number',
                            'name'  =>  'minimum_cart_value',
                            'label' =>  __( 'Minimum Cart Value' ),
                            'description'   =>  __( 'What is the minimum value of the cart to make this coupon eligible.' ),
                            'value' =>  $entry->minimum_cart_value ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'maximum_cart_value',
                            'label' =>  __( 'Maximum Cart Value' ),
                            'description'   =>  __( 'The value above which the current coupon can\'t apply.' ),
                            'value' =>  $entry->maximum_cart_value ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'valid_hours_start',
                            'label' =>  __( 'Valid Hours Start' ),
                            'description'   =>  __( 'Define form which hour during the day the coupons is valid.' ),
                            'value' =>  $entry->valid_hours_start ?? '',
                        ], [
                            'type'  =>  'text',
                            'name'  =>  'valid_hours_end',
                            'label' =>  __( 'Valid Hours End' ),
                            'description'   =>  __( 'Define to which hour during the day the coupons end stop valid.' ),
                            'value' =>  $entry->valid_hours_end ?? '',
                        ], [
                            'type'  =>  'number',
                            'name'  =>  'limit_usage',
                            'label' =>  __( 'Limit Usage' ),
                            'description'   =>  __( 'Define how many time a coupons can be redeemed.' ),
                            'value' =>  $entry->limit_usage ?? '',
                        ], 
                    ]
                ],
                'selected_products'  =>  [
                    'label' =>  __( 'Products' ),
                    'active'    =>  true,
                    'fields'    =>  [
                        [
                            'type'  =>  'multiselect',
                            'name'  =>  'products',
                            'options'   =>  Helper::toJsOptions( Product::get(), [ 'id', 'name' ]),
                            'label'     =>  __( 'Select Products' ),
                            'description'   =>  __( 'The following products will be required to be present on the cart, in order for this coupon to be valid.' )
                        ], 
                    ]
                ], 
                'selected_categories'  =>  [
                    'label' =>  __( 'Categories' ),
                    'active'    =>  false,
                    'fields'    =>  [
                        [
                            'type'  =>  'multiselect',
                            'name'  =>  'categories',
                            'options'   =>  Helper::toJsOptions( ProductCategory::get(), [ 'id', 'name' ]),
                            'label'     =>  __( 'Select Categories' ),
                            'description'   =>  __( 'The products assigned to one of these categories should be on the cart, in order for this coupon to be valid.' )
                        ], 
                    ]
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
        $inputs     =   collect( $inputs )->filter( function( $field, $key ) {
            if ( ( in_array( $key, [ 
                'minimum_cart_value',
                'maximum_cart_value',
                'assigned',
                'limit_usage',
            ]) && empty( $field ) ) || is_array( $field ) ) {
                return false;
            }
            return true;
        });

        return $inputs;
    }

    public function beforePost( Request $request )
    {
        foreach( $request->input( 'selected_products.products' ) as $product_id ) {
            $product    =   Product::find( $product_id );
            if ( ! $product instanceof Product ) {
                throw new Exception( __( 'Unable to save the coupon product as this product doens\'t exists.' ) );
            }
        }

        foreach( $request->input( 'selected_categories.categories' ) as $category_id ) {
            $category    =   ProductCategory::find( $category_id );
            if ( ! $category instanceof ProductCategory ) {
                throw new Exception( __( 'Unable to save the coupon category as this category doens\'t exists.' ) );
            }
        }
    }

    public function beforePut( Request $request )
    {
        foreach( $request->input( 'selected_products.products' ) as $product_id ) {
            $product    =   Product::find( $product_id );
            if ( ! $product instanceof Product ) {
                throw new Exception( __( 'Unable to save the coupon product as this product doens\'t exists.' ) );
            }
        }

        foreach( $request->input( 'selected_categories.categories' ) as $category_id ) {
            $category    =   ProductCategory::find( $category_id );
            if ( ! $category instanceof ProductCategory ) {
                throw new Exception( __( 'Unable to save the coupon category as this category doens\'t exists.' ) );
            }
        }
    }

    public function afterPost( Request $request, Coupon $coupon )
    {
        foreach( $request->input( 'selected_products.products' ) as $product_id ) {
            $productRelation                =   new CouponProduct;
            $productRelation->coupon_id     =   $coupon->id;
            $productRelation->product_id    =   $product_id;
            $productRelation->save();
        }

        foreach( $request->input( 'selected_categories.categories' ) as $category_id ) {
            $categoryRelation                =   new CouponCategory;
            $categoryRelation->coupon_id     =   $coupon->id;
            $categoryRelation->category_id  =   $category_id;
            $categoryRelation->save();
        }
    }

    /**
     * Filter PUT input fields
     * @param  array of fields
     * @return  array of fields
     */
    public function filterPutInputs( $inputs, Coupon $entry )
    {
        $inputs     =   collect( $inputs )->filter( function( $field, $key ) {
            if ( ( in_array( $key, [ 
                'minimum_cart_value',
                'maximum_cart_value',
                'assigned',
                'limit_usage',
            ]) && empty( $field ) ) || is_array( $field ) ) {
                return false;
            }
            return true;
        });

        return $inputs;
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
     * After Crud PUT
     * @param  object entry
     * @return  void
     */
    public function afterPut( $request, Coupon $coupon )
    {
        $coupon->categories->each( function( $category ) use ( $request ) {
            if ( ! in_array( $category->id, $request->input( 'selected_categories.categories' ) ) ) {
                $category->delete();
            }
        });

        $coupon->products->each( function( $product ) use ( $request ) {
            if ( ! in_array( $product->id, $request->input( 'selected_products.products' ) ) ) {
                $product->delete();
            }
        });

        foreach( $request->input( 'selected_products.products' ) as $product_id ) {
            $productRelation                  =   CouponProduct::where( 'coupon_id', $coupon->id )
                ->where( 'product_id', $product_id )
                ->first();

            if ( ! $productRelation instanceof CouponProduct ) {
                $productRelation                =   new CouponProduct;
            }

            $productRelation->coupon_id     =   $coupon->id;
            $productRelation->product_id    =   $product_id;
            $productRelation->save();
        }

        foreach( $request->input( 'selected_categories.categories' ) as $category_id ) {
            $categoryRelation                  =   CouponCategory::where( 'coupon_id', $coupon->id )
                ->where( 'category_id', $category_id )
                ->first();

            if ( ! $categoryRelation instanceof CouponCategory ) {
                $categoryRelation                =   new CouponCategory;
            }

            $categoryRelation->coupon_id     =   $coupon->id;
            $categoryRelation->category_id   =   $category_id;
            $categoryRelation->save();
        }
    }
    
    /**
     * Protect an access to a specific crud UI
     * @param  array { namespace, id, type }
     * @return  array | throw Exception
    **/
    public function canAccess( $fields )
    {
        $users      =   app()->make( Users::class );
        
        if ( $users->is([ 'admin' ]) ) {
            return [
                'status'    =>  'success',
                'message'   =>  __( 'The access is granted.' )
            ];
        }

        throw new Exception( __( 'You don\'t have access to that ressource' ) );
    }

    /**
     * Before Delete
     * @return  void
     */
    public function beforeDelete( $namespace, $id, Coupon $coupon ) {
        if ( $namespace == 'ns.coupons' ) {
            $coupon->categories()->delete();
            $coupon->products()->delete();
        }
    }

    /**
     * Define Columns
     * @return  array of columns configuration
     */
    public function getColumns() {
        return [
            'name'  =>  [
                'label'         =>  __( 'Name' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'type'              =>  [
                'label'         =>  __( 'Type' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'discount_value'  =>  [
                'label'         =>  __( 'Discount Value' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'valid_until'  =>  [
                'label'         =>  __( 'Valid Until' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'nexopos_users_username'        =>  [
                'label'         =>  __( 'Author' ),
                '$direction'    =>  '',
                '$sort'         =>  false
            ],
            'created_at'    =>  [
                'label'         =>  __( 'Created At' ),
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
        $entry->{ '$checked' }  =   false;
        $entry->{ '$toggled' }  =   false;
        $entry->{ '$id' }       =   $entry->id;
        
        switch( $entry->type ) {
            case 'percentage_discount': $entry->type = __( 'Percentage Discount' ); break;
            case 'flat_discount':       $entry->type = __( 'Flat Discount' ); break;
            default:                    $entry->type = __( 'N/A' ); break;
        }

        $entry->valid_until     =   $entry->valid_until ?? __( 'Unlimited' );

        // you can make changes here
        $entry->{'$actions'}    =   [
            [
                'label'         =>      __( 'Edit' ),
                'namespace'     =>      'edit.licence',
                'type'          =>      'GOTO',
                'index'         =>      'id',
                'url'           =>      url( '/dashboard/customers/coupons/edit/' . $entry->id )
            ], [
                'label'     =>  __( 'Delete' ),
                'namespace' =>  'delete',
                'type'      =>  'DELETE',
                'index'     =>  'id',
                'url'       =>  url( '/api/nexopos/v4/crud/ns.coupons/' . $entry->id ),
                'confirm'   =>  [
                    'message'  =>  __( 'Would you like to delete this ?' ),
                    'title'     =>  __( 'Delete a licence' )
                ]
            ]
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
        $user   =   app()->make( 'Tendoo\Core\Services\Users' );
        if ( ! $user->is([ 'admin', 'supervisor' ]) ) {
            return response()->json([
                'status'    =>  'failed',
                'message'   =>  __( 'You\'re not allowed to do this operation' )
            ], 403 );
        }

        if ( $request->input( 'action' ) == 'delete_selected' ) {
            $status     =   [
                'success'   =>  0,
                'failed'    =>  0
            ];

            foreach ( $request->input( 'entries_id' ) as $id ) {
                $entity     =   $this->model::find( $id );
                if ( $entity instanceof Coupon ) {
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

    /**
     * get Links
     * @return  array of links
     */
    public function getLinks()
    {
        return  [
            'list'      =>  'ns.coupons',
            'create'    =>  'ns.coupons/create',
            'edit'      =>  'ns.coupons/edit/#'
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
                'label'         =>  __( 'Delete Selected Coupons' ),
                'confirm'       =>  __( 'Would you like to delete selected coupons?' ),
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