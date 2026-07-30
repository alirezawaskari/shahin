<?php

namespace App\Services\V1\Admin;

use App\Models\Employee;
use App\Models\User;
use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class EmployeeService {
    /**
     * Summary of update
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update( Request $request, Employee $employee ) {
        $user = $employee->user;

        $request->validate( [
            'name' => 'string',
            'family' => 'string',
            'password' => 'nullable|string|min:6|confirmed',
            'show_status' => [ Rule::in( [ 0, 1 ] ) ],
            'status' => [ Rule::in( [ 0, 1 ] ) ],
        ] );

        $inputs = $request->all();

        $this->updateEmployeeData( $employee, $request, $inputs );
        $this->updateStatusAndLevel( $employee, $user, $request );
        $this->handleImageUploads( $user, $employee, $request, $inputs );

        $inputs = $this->cleanupInputs( $inputs );
        if ( isset( $request->wallet ) ) {
            $inputs[ 'wallet' ] = ( float ) str_replace( ',', '', $request->wallet );
        }
        $this->updatePassword( $user, $inputs );
        $user->update( $inputs );
        $this->syncRole( $user, $request->input( 'role_id' ) );

        return redirect()->back()->with( [ 'update' => 'success' ] );
    }
    /**
     * Summary of updateEmployeeData
     * @param Employee $employee
     * @param Request $request
     * @param array $inputs
     * @return void
     */
    protected function updateEmployeeData( Employee $employee, Request $request, array &$inputs ): void {
        $employeeData = $inputs[ 'employee' ] ?? [];

        if ( empty( $employeeData ) ) {

            return;

        }

        if ( !empty( $employeeData[ 'contract_start_date' ] ) ) {

            $employeeData[ 'contract_start_date' ] = Helper::vertaToMiladi( $employeeData[ 'contract_start_date' ] );

        }

        if ( !empty( $employeeData[ 'contract_expiration_date' ] ) ) {

            $employeeData[ 'contract_expiration_date' ] = Helper::vertaToMiladi( $employeeData[ 'contract_expiration_date' ] );

        }

        $employeeData[ 'parent_id' ] = $request->parent_id === 'null' ? null : $request->parent_id;

        // The branch select on the اطلاعات اداری tab writes users.center_id, but
        // Employee::center() / Center::employees() read employees.center_id — so the branch
        // page's staff list never saw anyone assigned here. Mirror the two.
        // Guarded by has(): the مالی and مدارک tabs post no center_id and must not null it.
        if ( $request->has( 'center_id' ) ) {
            $employeeData[ 'center_id' ] = $request->center_id ?: null;
        }

        $this->replaceImage( $request, $employee, 'urlimgaccess', 'imgaccess', 'document' );

        $this->replaceImage( $request, $employee, 'urlimglastcertificate', 'imglastcertificate', 'document' );

        $employee->update( $employeeData );

        unset( $inputs[ 'employee' ] );

    }
    /**
     * Summary of updateStatusAndLevel
     * @param Employee $employee
     * @param User $user
     * @param Request $request
     * @return void
     */
    public function updateStatusAndLevel( Employee $employee, User $user, Request $request ): void {

        $employee->update( [ 'show_status' => $request->show_status ] );

        if ( !is_null( $request->status ) ) {

            $employee->update( [ 'status' => $request->status ] );

        }

        $user->update( [ 'level' => $employee->status == 0 ? 'user' : 'admin' ] );

    }
    /**
     * Summary of handleImageUploads
     * @param Request $request
     * @param User $user
     * @param Employee $employee
     * @param array $inputs
     * @return void
     */
    public function handleImageUploads( User $user, Employee $employee,Request $request, array &$inputs ): void {
        $this->replaceImage( $request, $employee, 'urlimgaccess', 'imgaccess', 'document' );
        $this->replaceImage( $request, $employee, 'urlimglastcertificate', 'imglastcertificate', 'document' );
        $this->replaceImage( $request, $user, 'urlimgshenasname', 'imgshenasname', 'document' );
        $this->replaceImage( $request, $user, 'urlimgmeli', 'imgmeli', 'document' );
        $this->replaceImage( $request, $user, 'urlimgsignature', 'imgsignature', 'document' );
        $this->replaceImage( $request, $user, 'urlavatar', 'avatar', 'document' );
    }
    /**
    *
    * @param Request $request
    * @param User|Employee $model
    * @param string $inputField
    * @param string $dbField
    * @param string $destinationPath
    */
    public function replaceImage( Request $request, $model, string $inputField, string $dbField, string $destinationPath ): void {
        // The url* fields now carry a Spatie media id from the ajax upload (no longer a
        // file path), so attach that media to the owning model's single-file collection
        // instead of the legacy moveImage()+column write.
        $mediaId = $request->$inputField;
        if ( empty( $mediaId ) ) {
            return;
        }
        // $model is already the correct owner: the User for its KYC/avatar collections,
        // the Employee for imgaccess/imglastcertificate (verified against both callers).
        // Guard that it's a persisted HasMedia model before attaching.
        if ( !( $model instanceof \Spatie\MediaLibrary\HasMedia ) || !$model->exists ) {
            return;
        }
        MediaLibraryHelper::attachTo( $model, $mediaId, $dbField );
        // Field is a Spatie collection/accessor now — never mass-assign it as a column.
    }
    /**
     * Summary of handleUserImages
     * @param Request $request
     * @param User $user
     * @param array $inputs
     * @return void
     */
    public function handleUserImages( Request $request, User $user, array &$inputs ): void {
        // Spatie: attach each KYC/avatar image from its temp media id to the user's
        // single-file collection (replaces the legacy replaceImage/moveImage writes).
        foreach ( [
            'urlimgshenasname' => 'imgshenasname',
            'urlimgmeli'       => 'imgmeli',
            'urlimgsignature'  => 'imgsignature',
            'urlavatar'        => 'avatar',
        ] as $inputField => $collection ) {
            if ( !empty( $request->$inputField ) ) {
                \App\Helpers\MediaLibraryHelper::attachTo( $user, $request->$inputField, $collection );
            }
        }

        // Keep the image fields out of the mass-update — they're Spatie collections now.
        unset(
            $inputs['imgshenasname'], $inputs['imgmeli'], $inputs['imgsignature'], $inputs['avatar'],
            $inputs['urlimgshenasname'], $inputs['urlimgmeli'], $inputs['urlimgsignature'], $inputs['urlavatar']
        );
    }
    /**
     * Summary of cleanupInputs
     * @param array $inputs
     * @return array
     */
    public function cleanupInputs( array $inputs ): array {
        $unsetFields = [
            'urlimgshenasname', 'urlimgmeli', 'urlavatar',
            'urlimgsignature', 'urlimgaccess', 'urlimglastcertificate',
            'role_id', 'employee',
        ];

        foreach ( $unsetFields as $field ) {

            unset( $inputs[ $field ] );

        }

        return $inputs;

    }
    /**
     * Summary of updatePassword
     * @param User $user
     * @param array $inputs
     * @return void
     */
    public function updatePassword( User $user, array &$inputs ): void {

        if ( empty( $inputs[ 'password' ] ) ) {

            unset( $inputs[ 'password' ], $inputs[ 'password_confirmation' ] );

            return;

        }

        $user->update( [
            'password' => Hash::make( $inputs[ 'password' ] ),
        ] );

        unset( $inputs[ 'password' ], $inputs[ 'password_confirmation' ] );

    }
    /**
     * Summary of syncRole
     * @param User $user
     * @param mixed $roleId
     * @return void
     */
    public function syncRole( User $user, ?int $roleId ): void {

        if ( is_null( $roleId ) || $roleId == 0 ) {

            if ( $user->roles->isNotEmpty() ) {

                $user->removeRole( $user->roles[ 0 ]->id );

            }

        } else {

            $user->syncRoles( [ $roleId ] );

        }

    }
}
