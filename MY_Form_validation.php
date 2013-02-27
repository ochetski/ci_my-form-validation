<?php defined('BASEPATH') OR exit('No direct script access allowed');

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
	 * @param	string	$str
	 * @return	bool
	 */
	public function valid_phone($str)
	{
		$regex = '~^(0?(\d{2})|\(0?(\d{2})\))[^\)\d]?[ ]?(\d{4,5}[ \.\-]?\d{4})$~ix';

		# clean non numeric
		$str = preg_replace('~[^0-9]+~', '', $str);

		return (!preg_match($regex, $str) ? FALSE : $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Match valid CPF format
	 *
	 * @access	public
	 * @param	string	$str
	 * @return	bool
	 */
	public function valid_cpf($str)
	{
		# check format
		if(!preg_match('~^([0-9]{11}|[0-9]{3}\.[0-9]{3}\.[0-9]{3}\-[0-9]{2})$~i', $str)) {
			return FALSE;
		}

		# clean non numeric
		$str = preg_replace('~[^0-9]+~', '', $str);

		# returns false if it's an invalid number
		if(preg_match('^(01234567890|12345678909|(\d)\2{10})$', $str)) {
			return FALSE;
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
			return FALSE;
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
			return FALSE;
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Match valid CNPJ format
	 *
	 * @access	public
	 * @param	string	$str
	 * @return	bool
	 */
	public function valid_cnpj($str)
	{
		# check format (xx.xxx.xxx/xxxx-xx or 14 digits)
		if(!preg_match('~^([0-9]{14}|[0-9]{2,3}\.[0-9]{3}\.[0-9]{3}\/[0-9]{4}\-[0-9]{2})$~i', $str)) {
			return FALSE;
		}

		# clean non numeric
		$str = preg_replace('/[^0-9]+/si', '', $str);

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

		return ($str{12} == $digit1 && $str{13} == $digit2) ? $str : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Funcao para formatar datas ou outros dados atraves de regex
	 * Date will also be tested if defined numerical values for month, year and day
	 *
	 * @param	string	$str
	 * @param	string	$format	using php format with preceded %, default %d/%m/%Y
	 */
	public function valid_date($str, $format = '%d/%m/%Y')
	{
		$pattern = str_replace(
			array('%d', '%D', '%j', '%m', '%M', '%n', '%Y', '%y', '%G', '%g', '%H', '%h', '%i', '%s'),
			array('(?P<d>\d{2})', '(?P<D>\w{3})', '(?P<j>\d{1,2})', '(?P<m>\d{2})', '(?P<M>\w{3})', '(?P<n>\d{1,2})', '(?P<Y>\d{4})', '(?P<y>\d{2})', '(?P<G>\d{1,2})', '(?P<g>\d{1,2})', '(?P<H>\d{2})', '(?P<h>\d{2})', '(?P<i>\d{2})', '(?P<s>\d{2})'),
			addslashes($format)
		);

		if(preg_match("~{$pattern}~s", $str, $matches))
		{
			if(preg_match('~^.*?(%([dj]).*?%([mn]).*?%(Y)|%([dj]).*?%(Y).*?%([mn])|%([mn]).*?%(Y).*?%([dj])|%([mn]).*?%([dj]).*?%(Y)|%(Y).*?%([dj]).*?%([mn])|%(Y).*?%([mn]).*?%([dj])).*$~s', $format))
			{
				$d = isset($matches['d']) ? $matches['d'] : $matches['j'];
				$m = isset($matches['m']) ? $matches['m'] : $matches['n'];
				$y = $matches['Y'];

				if(checkdate($m, $d, $y))
				{
					return $str;
				}
				else
				{
					return FALSE;
				}
			}
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Match one field to another
	 * Add an exception to the same pk
	 *
	 * @access	public
	 * @param	string
	 * @param	field
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

	// --------------------------------------------------------------------

	/**
	 * Checks if the field exists in db column
	 *
	 * @access	public
	 * @param	string	$str
	 * @param	mixed	$field
	 * @return	bool
	 */
	public function is_valid($str, $field)
	{
		list($table, $field)=explode('.', $field);
		$query = $this->CI->db->limit(1)->get_where($table, array($field => $str));

		return $query->num_rows() !== 0;
	}

}

/* End of file MY_Form_validation.php */
/* Location: ./application/libraries/MY_Form_validation.php */