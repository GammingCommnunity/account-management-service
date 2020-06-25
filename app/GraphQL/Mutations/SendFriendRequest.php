<?php

namespace App\GraphQL\Mutations;

use App\Account;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use App\AccountRelationship;
use App\Enums\DbEnums\AccountPrivacyType;
use App\Enums\DbEnums\AccountRelationshipType;
use Illuminate\Database\Eloquent\Collection;

class SendFriendRequest
{
	/**
	 * Return a value for the field.
	 *
	 * @param  null  $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
	 * @param  mixed[]  $args The arguments that were passed into the field.
	 * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context Arbitrary data that is shared between all fields of a single query.
	 * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
	 * @return bool
	 */
	public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
	{
		$result = false;
		$receiverId = $args['receiver_id'];
		$currentAccount = $rootValue['verified_account'];

		if ($currentAccount && $receiverId !== $currentAccount->id && Account::find($receiverId)) {
			$relationship = AccountRelationship::where('relationship_type', '<>', AccountRelationshipType::STRANGER)
				->where(function ($query) use ($receiverId, $currentAccount) {
					return $query
						->orWhere(function ($query) use ($receiverId, $currentAccount) {
							return $query
								->where('sender_account_id', $receiverId)
								->where('relationship_type', '<>', AccountRelationshipType::FRIEND_REQUEST)
								->where('receiver_account_id', $currentAccount->id);
						})
						->orWhere(function ($query) use ($receiverId, $currentAccount) {
							return $query
								->where('receiver_account_id', $receiverId)
								->where('sender_account_id', $currentAccount->id);
						});
				})
				// ->toSql();
				// dd($relationship);
				->first('id');

			if (!$relationship) {
				$result = true == AccountRelationship::create([
					'sender_account_id' => $currentAccount->id,
					'receiver_account_id' => $receiverId,
					'relationship_type' => AccountRelationshipType::FRIEND_REQUEST
				]);

				if($result){
					FollowAccount::follow($receiverId, $currentAccount);
				}
			}
		}

		return $result;
	}
}
