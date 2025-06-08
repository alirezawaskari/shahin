<?php

namespace App\Helpers;

use App\Models\Installment;

class CalculationHelper {
    /**
    * @param $supervisor_factor_item
    * @param $total_price
    * @param $user
    * @param $type
    * @return int
    * calculate prepayment from total price
    */
    public static function calculatePrepayment( $supervisor_factor_item, $total_price, $type, $user ) {
        $prepayment_percent = self::getPrepaymentPercent( $user, $supervisor_factor_item );
        $prepayment = $total_price * $prepayment_percent;

        $prepayment_price = $type ? $prepayment : $total_price;

        return $prepayment_price;
    }
    /**
    * @param $supervisor_factor_item
    * @param $total_price
    * @param $prepayment_price
    * @param $installments_num
    * @return int
    */
    public static function calculateWage( $supervisor_factor_item, $total_price, $prepayment_price, $installments_num ) {
        $loan = $total_price - $prepayment_price;
        $wage_percent = self::getWagePercent( $supervisor_factor_item );

        return $loan * $wage_percent / 12 * $installments_num;
    }
    /**
    * @param $wage
    * @return int
    */
    public static function calculateTax( $wage ) {
        return $wage * 0.09;
    }
    /**
    * @param $total_price
    * @param $prepayment_price
    * @param $wage
    * @param $tax
    * @return int
    */
    public static function calculateLoan( $total_price, $prepayment_price, $wage, $tax ) {
        return $total_price - $prepayment_price + $wage + $tax;
    }
    /**
    * @param $type
    * @param $loan
    * @param $installments_num
    * @return int
    */
    public static function calculateInstallmentsPrice( $type, $loan, $installments_num ) {
        return $type ? ( $loan / $installments_num ) : 0;
    }
    /**
    * @param $supervisor_factor_item
    * @param $type
    * @return int
    */
    public static function calculatePrepaymentNum( $supervisor_factor_item, $type ) {
        $prepayment_num = $supervisor_factor_item->serviceTariffs()->sum( 'factor_item_service_tariff.session_number' ) != 0 ? $supervisor_factor_item->serviceTariffs()->sum( 'factor_item_service_tariff.session_number' ) : 1;
        return $type ? 3 : $prepayment_num;
    }
    /**
    * @param $supervisor_factor_item
    * @return int
    * set installments num by financialPlansType if exist else getInstallmentsNum from installments table
    */
    public static function calculateInstallmentsNum( $supervisor_factor_item ) {
        if ( $supervisor_factor_item->financialPlansType && $supervisor_factor_item->financialPlansType->number_pay_steps ) {
            return $supervisor_factor_item->financialPlansType->number_pay_steps;
        }
        return Installment::getInstallmentNum( $supervisor_factor_item->total_price, $supervisor_factor_item->factoritemable );
    }
    /**
    * @param $supervisor_factor_item
    * @return float
    */
    private static function getWagePercent( $supervisor_factor_item ) {
        return $supervisor_factor_item->factoritemable->wage_percent/100;
    }
    /**
    * @param $user
    * @param $supervisor_factor_item
    * @return float
    * get prepayment percent by financialPlansType or factoritemable item
    * check organization percent if exists
    */
    private static function getPrepaymentPercent( $user, $supervisor_factor_item ) {
        if ( $user->organization_id ) {
            return $user->organization->percent / 100;
        }

        $percent = $supervisor_factor_item->financialPlansType
        ? $supervisor_factor_item->financialPlansType->prepayment_percent
        : $supervisor_factor_item->factoritemable->percent;

        return $percent / 100;
    }
    /**
    * @param mixed $sessions_sum_price
    * @param mixed $prepayment_paid
    * @return int|float
    */
    public static function getExtraWorkFactorItem( $sessions_sum_price, $prepayment_paid ) {
        $extra_work_price = $sessions_sum_price - $prepayment_paid;
        return $extra_work_price > 0 ? $extra_work_price : 0;
    }
}
