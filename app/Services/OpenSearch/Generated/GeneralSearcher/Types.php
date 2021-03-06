<?php
namespace App\Services\OpenSearch\Generated\GeneralSearcher;

/**
 * Autogenerated by Thrift Compiler (0.10.0)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use App\Services\OpenSearch\Thrift\Base\TBase;
use App\Services\OpenSearch\Thrift\Type\TType;
use App\Services\OpenSearch\Thrift\Type\TMessageType;
use App\Services\OpenSearch\Thrift\Exception\TException;
use App\Services\OpenSearch\Thrift\Exception\TProtocolException;
use App\Services\OpenSearch\Thrift\Protocol\TProtocol;
use App\Services\OpenSearch\Thrift\Protocol\TBinaryProtocolAccelerated;
use App\Services\OpenSearch\Thrift\Exception\TApplicationException;


class SearchResult {
  static $_TSPEC;

  /**
   * @var string
   */
  public $result = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'result',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['result'])) {
        $this->result = $vals['result'];
      }
    }
  }

  public function getName() {
    return 'SearchResult';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->result);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('SearchResult');
    if ($this->result !== null) {
      $xfer += $output->writeFieldBegin('result', TType::STRING, 1);
      $xfer += $output->writeString($this->result);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

