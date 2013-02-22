<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MY Form Validation
 *
 * Add new validation methods to CI default Form Validation
 *
 * @author	William Ochetski Hellas
 */
class MY_Form_validation extends CI_Form_validation
{

	function __construct()
	{
		parent::__construct();
	}

	// --------------------------------------------------------------------

	/**
	 * Match valid brazilian phone format
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function valid_phone($str)
	{
		$regex = '~^(0?(\d{2})|\(0?(\d{2})\))[^\)\d]?[ ]?(\d{4,5}[ \.\-]?\d{4})$~ix';

		# clean non numeric
		$str = preg_replace('~[^0-9]+~', '', $str);

		return (!preg_match($regex, $str) ? false : $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Match valid CPF format
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function valid_cpf($str)
	{
		$nulos = array('01234567890', '12345678909','11111111111','22222222222','33333333333','44444444444','55555555555','66666666666','77777777777','88888888888','99999999999','00000000000');

		# check format
		if(!preg_match('~^([0-9]{11}|[0-9]{3}\.[0-9]{3}\.[0-9]{3}\-[0-9]{2})$~i', $str)) {
			return false;
		}

		# clean non numeric
		$str = preg_replace('~[^0-9]+~', '', $str);

		# returns false if it's null
		if(in_array($str, $nulos)) {
			return false;
		}

		# calculate the penultimate verificator digit
		$acum = 0;
		for($i = 0; $i < 9; $i++) {
			$acum += $str{$i} * (10 - $i);
		}
		$x = $acum % 11;
		$acum = ($x > 1) ? (11 - $x) : 0;

		# returns false if calculated digit is invalid
		if($acum != $str{9}) {
			return false;
		}

		# calculate the last verificator digit
		$acum = 0;
		for($i = 0; $i < 10; $i++) {
			$acum += $str{$i} * (11 - $i);
		}
		$x = $acum % 11;
		$acum = ($x > 1) ? (11 - $x) : 0;

		# returns false if calculated digit is invalid
		if($acum != $str{10}) {
			return false;
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Match valid CNPJ format
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function valid_cnpj($str)
	{
		# check format (xx.xxx.xxx/xxxx-xx or 14 digits)
		if(!preg_match('~^([0-9]{14}|[0-9]{2,3}\.[0-9]{3}\.[0-9]{3}\/[0-9]{4}\-[0-9]{2})$~i', $str)) {
			return false;
		}

		# clean non numeric
		$str = preg_replace('/[^0-9]/si', '', $str);

		# calculate the penultimate verificator digit
		$sum1 = ($str{0} * 5) +
				($str{1} * 4) +
				($str{2} * 3) +
				($str{3} * 2) +
				($str{4} * 9) +
				($str{5} * 8) +
				($str{6} * 7) +
				($str{7} * 6) +
				($str{8} * 5) +
				($str{9} * 4) +
				($str{10} * 3) +
				($str{11} * 2);
		$last = $sum1 % 11;
		$digit1 = $last < 2 ? 0 : 11 - $last;

		# calculate the last verificator digit
		$sum2 = ($str{0} * 6) +
				($str{1} * 5) +
				($str{2} * 4) +
				($str{3} * 3) +
				($str{4} * 2) +
				($str{5} * 9) +
				($str{6} * 8) +
				($str{7} * 7) +
				($str{8} * 6) +
				($str{9} * 5) +
				($str{10} * 4) +
				($str{11} * 3) +
				($str{12} * 2);
		$last = $sum2 % 11;
		$digit2 = $last < 2 ? 0 : 11 - $last;

		return ($str{12} == $digit1 && $str{13} == $digit2) ? $str : false;
	}

	// --------------------------------------------------------------------

	/**
	 * Match one field to another
	 * Add an exception to the same pk
	 *
	 * @access	public
	 * @param	string
	 * @param	field
	 * @param	string
	 * @param	mixed
	 * @return	bool
	 */
	public function is_unique($str, $field)
	{
		# separate arguments
		$field = explode(',', $field);

		# if there are pk and it's key
		if(!empty($field[1]) && !empty($field[2])) {
			$this->CI->db->where($field[1].' !=', $field[2]);
		}

		# anyway do as usual
		return parent::is_unique($str, $field[0]);
	}

}

/* End of file MY_Form_validation.php */
/* Location: ./application/libraries/MY_Form_validation.php */