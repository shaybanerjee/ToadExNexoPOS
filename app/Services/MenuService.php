<?php

namespace App\Services;

use Illuminate\Support\Facades\Gate;
use TorMorten\Eventy\Facades\Eventy as Hook;

class MenuService
{
    protected $menus;

    public function buildMenus()
    {
        $this->menus = [
            'dashboard' => [
                'label' => __( 'Dashboard' ),
                'permissions' => [ 'read.dashboard' ],
                'icon' => 'la-home',
                'childrens' => [
                    'index' => [
                        'label' => __( 'Home' ),
                        'permissions' => [ 'read.dashboard' ],
                        'href' => ns()->url( '/dashboard' ),
                    ],
                ],
            ],
            'pos' => [
                'label' => __( 'POS' ),
                'icon' => 'la-cash-register',
                'permissions' => [ 'nexopos.create.orders' ],
                'href' => ns()->url( '/dashboard/pos' ),
            ],
            'inventory' => [
                'label' => __( 'Inventory' ),
                'icon' => 'la-boxes',
                'permissions' => [
                    'nexopos.read.products',
                    'nexopos.create.products',
                    'nexopos.read.categories',
                    'nexopos.create.categories',
                    'nexopos.read.products-units',
                    'nexopos.create.products-units',
                    'nexopos.read.products-units',
                    'nexopos.create.products-units',
                    'nexopos.make.products-adjustments',
                ],
                'childrens' => [
                    'products' => [
                        'label' => __( 'Products' ),
                        'permissions' => [ 'nexopos.read.products' ],
                        'href' => ns()->url( '/dashboard/products' ),
                    ],
                    'create-products' => [
                        'label' => __( 'Create Product' ),
                        'permissions' => [ 'nexopos.create.products' ],
                        'href' => ns()->url( '/dashboard/products/create' ),
                    ],
                    'labels-printing' => [
                        'label' => __( 'Print Labels' ),
                        'href' => ns()->url( '/dashboard/products/print-labels' ),
                        'permissions' => [ 'nexopos.create.products-labels' ],
                    ],
                    'categories' => [
                        'label' => __( 'Categories' ),
                        'permissions' => [ 'nexopos.read.categories' ],
                        'href' => ns()->url( '/dashboard/products/categories' ),
                    ],
                    'create-categories' => [
                        'label' => __( 'Create Category' ),
                        'permissions' => [ 'nexopos.create.categories' ],
                        'href' => ns()->url( '/dashboard/products/categories/create' ),
                    ],
                    'units' => [
                        'label' => __( 'Units' ),
                        'permissions' => [ 'nexopos.read.products-units' ],
                        'href' => ns()->url( '/dashboard/units' ),
                    ],
                    'create-units' => [
                        'label' => __( 'Create Unit' ),
                        'permissions' => [ 'nexopos.create.products-units' ],
                        'href' => ns()->url( '/dashboard/units/create' ),
                    ],
                    'unit-groups' => [
                        'label' => __( 'Unit Groups' ),
                        'permissions' => [ 'nexopos.read.products-units' ],
                        'href' => ns()->url( '/dashboard/units/groups' ),
                    ],
                    'create-unit-groups' => [
                        'label' => __( 'Create Unit Groups' ),
                        'permissions' => [ 'nexopos.create.products-units' ],
                        'href' => ns()->url( '/dashboard/units/groups/create' ),
                    ],
                    'stock-adjustment' => [
                        'label' => __( 'Stock Adjustment' ),
                        'permissions' => [ 'nexopos.make.products-adjustments' ],
                        'href' => ns()->url( '/dashboard/products/stock-adjustment' ),
                    ],
                    'product-history' => [
                        'label' => __( 'Stock Flow Records' ),
                        'permissions' => [ 'nexopos.read.products' ],
                        'href' => ns()->url( '/dashboard/products/stock-flow-records' ),
                    ],
                ],
            ],
            'taxes' => [
                'label' => __( 'Taxes' ),
                'icon' => 'la-balance-scale-left',
                'permissions' => [
                    'nexopos.create.taxes',
                    'nexopos.read.taxes',
                    'nexopos.update.taxes',
                    'nexopos.delete.taxes',
                ],
                'childrens' => [
                    'taxes-groups' => [
                        'label' => __( 'Taxes Groups' ),
                        'permissions' => [ 'nexopos.read.taxes' ],
                        'href' => ns()->url( '/dashboard/taxes/groups' ),
                    ],
                    'create-taxes-group' => [
                        'label' => __( 'Create Tax Groups' ),
                        'permissions' => [ 'nexopos.create.taxes' ],
                        'href' => ns()->url( '/dashboard/taxes/groups/create' ),
                    ],
                    'taxes' => [
                        'label' => __( 'Taxes' ),
                        'permissions' => [ 'nexopos.read.taxes' ],
                        'href' => ns()->url( '/dashboard/taxes' ),
                    ],
                    'create-tax' => [
                        'label' => __( 'Create Tax' ),
                        'permissions' => [ 'nexopos.create.taxes' ],
                        'href' => ns()->url( '/dashboard/taxes/create' ),
                    ],
                ],
            ],
            'users' => [
                'label' => __( 'Users' ),
                'icon' => 'la-users',
                'childrens' => [
                    'profile' => [
                        'label' => __( 'My Profile' ),
                        'permissions' => [ 'manage.profile' ],
                        'href' => ns()->url( '/dashboard/users/profile' ),
                    ],
                    'users' => [
                        'label' => __( 'Users List' ),
                        'permissions' => [ 'read.users' ],
                        'href' => ns()->url( '/dashboard/users' ),
                    ],
                    'create-user' => [
                        'label' => __( 'Create User' ),
                        'permissions' => [ 'create.users' ],
                        'href' => ns()->url( '/dashboard/users/create' ),
                    ],
                ],
            ],
        ];
    }

    /**
     * returns the list of available menus
     *
     * @return array of menus
     */
    public function getMenus()
    {
        $this->buildMenus();
        $this->menus = Hook::filter( 'ns-dashboard-menus', $this->menus );
        $this->toggleActive();

        return collect( $this->menus )->filter( function ( $menu ) {
            return ! isset( $menu[ 'permissions' ] ) || Gate::any( $menu[ 'permissions' ] );
        } )->map( function ( $menu ) {
            $menu[ 'childrens' ] = collect( $menu[ 'childrens' ] ?? [] )->filter( function ( $submenu ) {
                return ! isset( $submenu[ 'permissions' ] ) || Gate::any( $submenu[ 'permissions' ] );
            } )->toArray();

            return $menu;
        } );
    }

    /**
     * Will make sure active menu
     * is toggled
     *
     * @return void
     */
    public function toggleActive()
    {
        foreach ( $this->menus as $identifier => &$menu ) {
            if ( isset( $menu[ 'href' ] ) && $menu[ 'href' ] === url()->current() ) {
                $menu[ 'toggled' ] = true;
            }

            if ( isset( $menu[ 'childrens' ] ) ) {
                foreach ( $menu[ 'childrens' ] as $subidentifier => &$submenu ) {
                    if ( $submenu[ 'href' ] === url()->current() ) {
                        $menu[ 'toggled' ] = true;
                        $submenu[ 'active' ] = true;
                    }
                }
            }
        }
    }
}
