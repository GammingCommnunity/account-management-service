<?php

namespace App\GraphQL\Mutations;

use App\AccountInfoBirthMonth;
use App\AccountInfoBirthYear;
use App\AccountInfoEmail;
use App\AccountInfoPhone;
use App\Enums\AccountEditingResultStatus;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use App\GraphQL\Entities\Input\AccountBirthMonthInput;
use App\GraphQL\Entities\Input\AccountBirthYearInput;
use App\GraphQL\Entities\Input\AccountEmailInput;
use App\GraphQL\Entities\Input\AccountPhoneInput;
use App\GraphQL\Entities\Input\AccountSettingInput;
use App\GraphQL\Entities\Result\AccountEditingResult;
use App\GraphQL\Entities\Result\BooleanResult;

class EditAccount
{
	/**
	 * Return a value for the field.
	 *
	 * @param  null  $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
	 * @param  mixed[]  $args The arguments that were passed into the field.
	 * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context Arbitrary data that is shared between all fields of a single query.
	 * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
	 * @return AccountEditingResult
	 */
	public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): AccountEditingResult
	{
		$result = new AccountEditingResult();
		$account = $rootValue['verified_account'];
		$inputAccount = $args['account'];

		if ($account) {
			$birthMonth = isset($inputAccount['birth_month']) ? new AccountBirthMonthInput($args) : null;
			$birthYear = isset($inputAccount['birth_year']) ? new AccountBirthYearInput($args) : null;
			$phone = isset($inputAccount['phone']) ? new AccountPhoneInput($args) : null;
			$email = isset($inputAccount['email']) ? new AccountEmailInput($args) : null;
			$setting = isset($inputAccount['setting']) ? new AccountSettingInput($args) : null;

			if (isset($inputAccount['name'])) {
				$account->name = $inputAccount['name'];
			}
			if (isset($inputAccount['describe'])) {
				$account->describe = $inputAccount['describe'];
			}
			if ($birthMonth) {
				if ($account->birthMonth) {
					$account->birthMonth->month = $birthMonth->month;
					$account->birthMonth->account_privacy_type_id = $birthMonth->privacyTypeId;
					if (!$account->birthMonth->save()) {
						$result->describe = 'Unable to save account birth month';
					}
				} else {
					$newBirthMonth = AccountInfoBirthMonth::create([
						'month' => $birthMonth->month,
						'account_privacy_type_id' => $birthMonth->privacyTypeId
					]);
					if ($newBirthMonth) {
						$account->account_info_birth_month_id = $newBirthMonth->id;
					} else {
						$result->describe = 'Unable to create account birth month';
					}
				}
			}
			if ($birthYear) {
				if ($account->birthYear) {
					$account->birthYear->year = $birthYear->year;
					$account->birthYear->account_privacy_type_id = $birthYear->privacyTypeId;
					if (!$account->birthYear->save()) {
						$result->describe = 'Unable to save account birth year';
					}
				} else {
					$newBirthYear = AccountInfoBirthYear::create([
						'year' => $birthYear->year,
						'account_privacy_type_id' => $birthYear->privacyTypeId
					]);
					if ($newBirthYear) {
						$account->account_info_birth_year_id = $newBirthYear->id;
					} else {
						$result->describe = 'Unable to create account birth year';
					}
				}
			}
			if ($email) {
				if ($account->email) {
					$account->email->email = $email->email;
					$account->email->account_privacy_type_id = $email->privacyTypeId;
					if (!$account->email->save()) {
						$result->describe = 'Unable to save account email';
					}
				} else {
					$newEmail = AccountInfoEmail::create([
						'email' => $email->email,
						'account_privacy_type_id' => $email->privacyTypeId
					]);
					if ($newEmail) {
						$account->account_info_email_id = $newEmail->id;
					} else {
						$result->describe = 'Unable to create account email';
					}
				}
			}
			if ($phone) {
				if ($account->phone) {
					$account->phone->phone = $phone->phone;
					$account->phone->account_privacy_type_id = $phone->privacyTypeId;
					if (!$account->phone->save()) {
						$result->describe = 'Unable to save account phone';
					}
				} else {
					$newPhone = AccountInfoPhone::create([
						'phone' => $phone->phone,
						'account_privacy_type_id' => $phone->privacyTypeId
					]);
					if ($newPhone) {
						$account->account_info_phone_id = $newPhone->id;
					} else {
						$result->describe = 'Unable to create account phone';
					}
				}
			}
			if ($setting) {
				if ($account->setting) {
					$account->setting->anonymous = $setting->anonymous;
					if (!$account->setting->save()) {
						$result->describe = 'Unable to save account setting';
					}
				} else {
					$result->describe = 'The account setting does not exist';
				}
			}

			if ($account->save()) {
				$result->status = AccountEditingResultStatus::SUCCESS;
			} else {
				$result->describe = 'Unable to save account';
				//must delete ralationship with account table if it unable to save
				if(isset($newBirthMonth) && $newBirthMonth){
					$newBirthMonth->delete();
				}
				if(isset($newBirthYear) && $newBirthYear){
					$newBirthYear->delete();
				}
				if(isset($newPhone) && $newPhone){
					$newPhone->delete();
				}
				if(isset($newEmail) && $newEmail){
					$newEmail->delete();
				}
			}
		} else {
			$result->status = AccountEditingResultStatus::ACC_NOT_FOUND;
		}

		return $result;
	}
}
