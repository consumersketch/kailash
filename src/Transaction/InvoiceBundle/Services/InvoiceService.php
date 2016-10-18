<?php

/**
 * @package     Invoice 
 * @author      Kailash
 */

namespace Transaction\InvoiceBundle\Services;

use Doctrine\ORM\EntityManager;

class InvoiceService {
    
    /* Initiliaze varibles */

    protected $em;

    /**
     * 
     * @param EntityManager $em
     */
    function __construct(EntityManager $em) {
        $this->em = $em;
        $this->filterParam = array();
    }

    /**
     * Un set varibles
     */
    function __destruct() {
        unset($param);
        unset($sql);
        unset($stmt);
        unset($lastYear);
        unset($this->filterParam);
    }

    /**
     * This function return all possible invoice list
     * @param type $request
     * @return result/boolean
     */
    function invoiceList($request) {
        $this->prepareFilterArray($request);

        return $this->getInvoiceList();
    }

    /**
     * This function used to Prepare Filter variables which is used by InvoiceList
     * @param type $request
     */
    function prepareFilterArray($request) {
        $this->relativeDateFilter($request->request->get('relativeDate', ''));
        $this->clientFilter($request->request->get('client', ''));
        $this->productFilter($request->request->get('product', ''));
    }

    /**
     * Set varible for Client Filter
     * @param type $client
     */
    function clientFilter($client) {
        $this->filterParam['client'] = $client;
    }

    /**
     * Set variable for Product filter
     * @param type $product
     */
    function productFilter($product) {
        $this->filterParam['product'] = $product;
    }

    /**
     * set variable for Relative filter
     * @param type $relativeDate
     */
    function relativeDateFilter($relativeDate) {
        switch ($relativeDate) {
            case 1:
                /* for Last Month to Date */
                $relativeMonth = date('Y-m-d', strtotime(date('Y-m-d') . " -1 month"));
                $relativeMonth = date('Y-m-d', strtotime(date($relativeMonth) . " -1 day"));
                $this->filterParam['date'] = ['from' => $relativeMonth, 'to' => date('Y-m-d')];
                break;
            case 2:
                /* For This Month */
                $this->filterParam['date'] = ['from' => date('Y-m-01'), 'to' => date('Y-m-d')];
                break;
            case 3:
                /* For This Year */
                $this->filterParam['date'] = ['from' => date('Y-01-01'), 'to' => date('Y-m-d')];
                break;
            case 4:
                /* For Last Year */
                $lastYear = date("Y", strtotime("-1 year"));
                $this->filterParam['date'] = ['from' => date($lastYear . '-01-01'), 'to' => date($lastYear . '-12-31')];
                break;
            default:
                $this->filterParam['date'] = "";
                break;
        }
    }

    /**
     * This function get all the possible list of invoice data from database
     * @return result/boolean
     */
    function getInvoiceList() {
        $bindValues = array();

        $sql = " SELECT i.invoice_num, i.invoice_date,ilt.product_id,  p.product_description, ilt.qty, ilt.price"
                . " FROM invoices i"
                . " JOIN invoicelineitems ilt ON i.invoice_num  = ilt.invoice_num "
                . " LEFT JOIN  products p on  p.product_id =  ilt.product_id   WHERE i.invoice_num IS NOT NULL ";

        if (!empty($this->filterParam['date'])) {
            $sql .= " AND i.invoice_date BETWEEN   ? AND ?";
            $bindValues[] = $this->filterParam['date']['from'];
            $bindValues[] = $this->filterParam['date']['to'];
        }

        if (!empty($this->filterParam['client'])) {
            $sql .= " AND i.client_id  =    ?  ";
            $bindValues[] = $this->filterParam['client'];
        }

        if (!empty($this->filterParam['product'])) {
            $sql .= " AND ilt.product_id  =    ?  ";
            $bindValues[] = $this->filterParam['product'];
        }

        $sql.= "order by invoice_date desc";

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute($bindValues);
        $result = $stmt->fetchAll();

        unset($sql);
        unset($stmt);
        unset($bindValues);

        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * This function used to Fetch All clients 
     * @return result/boolean
     */
    function getClients() {
        $sql = " SELECT c.client_id, c.client_name"
                . " FROM  clients c  "
                . " WHERE c.client_id IS NOT NULL";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        unset($sql);
        unset($stmt);

        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * This function used to Fetch All Product by client specific
     * @param clientId
     * @return result/boolean
     */
    function getProductsByclient() {
        $bindValues = array();
        if (!empty($this->filterParam['client'])) {

            $sql = " SELECT p.product_id, p.product_description "
                    . " FROM  products p  "
                    . " WHERE p.client_id  = ? ";

            $bindValues[] = $this->filterParam['client'];
            $stmt = $this->em->getConnection()->prepare($sql);
            $stmt->execute($bindValues);
            $result = $stmt->fetchAll();

            unset($sql);
            unset($stmt);
            unset($bindValues);

            if ($result) {
                return $result;
            }
        }

        return false;
    }

}
