<?php
namespace addons\content_api\v1\invoice\tools;
use \lib\utility;
use \lib\debug;
use \lib\db\logs;

trait add
{

	use invoice_check_args;
	/**
	 * Adds a invoice.
	 *
	 * @param      array    $_args  The arguments
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function add_invoice($_args = [])
	{

		// ready to insert invoiceteam or invoicebranch record
		$args                  = [];

		// default args
		$default_args =
		[
			'method'   => 'post',
			'debug'    => true,
			'save_log' => true,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}
		// merge default args and args
		$_args = array_merge($default_args, $_args);

		// set default title of debug
		if($_args['debug']) debug::title(T_("Operation Faild"));

		// set the log meta
		$log_meta =
		[
			'data' => null,
			'meta' =>
			[
				'user_id' => $this->user_id,
				'input'   => utility::request(),
			]
		];

		// check invoice id is exist
		if(!$this->user_id)
		{
			if($_args['save_log']) logs::set('addon:api:invoice:user_id:notfound', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("invoice not found"), 'invoice', 'permission');
			return false;
		}


		/**
		 * check and set the args
		 */
		$return_function = $this->invoice_check_args($_args, $args, $log_meta);

		if(!debug::$status || $return_function === false)
		{
			return false;
		}

		$invoice_detail = $args['details'];
		unset($args['details']);

		// return result
		// sum invoice
		$return = [];

		// insert new invoice team
		if($_args['method'] === 'post')
		{
			$invoice = new \lib\db\invoices;
	        $invoice->add($args);
	        foreach ($invoice_detail as $key => $value)
	        {
		        $invoice->add_child($value);
	        }
	        $invoice_id = $invoice->save();

	        if(isset($args['total']))
	        {
	        	$return['total'] = $args['total'];
	        }

	        // send notification
	        $notifi_text = T_("You have new invoice in azvir.com");
	        $this->send_notification(['text' => $notifi_text, 'cat' => 'invoice', 'to' => $args['user_id'], 'send_to_admin' => true]);

		}
		elseif($_args['method'] === 'patch')
		{

			$id = utility::request('id');
			$id = utility\shortURL::decode($id);
			if(!$id)
			{
				if($_args['save_log']) logs::set('addon:api:invoice:pathc:id:not:set', $this->user_id, $log_meta);
				if($_args['debug']) debug::error(T_("Id not set"), 'id', 'arguments');
				return false;
			}

			if(!empty($args))
			{
				\lib\db\invoices::update($args, $id);
			}
		}

		if(debug::$status)
		{
			if($_args['debug']) debug::title(T_("Operation Complete"));

			if($_args['method'] === 'post')
			{
				if($_args['debug']) debug::true(T_("invoice successfully added"));
			}
			elseif($_args['method'] === 'patch')
			{
				if($_args['debug']) debug::true(T_("invoice successfully updated"));
			}
		}

		return $return;
	}
}
?>