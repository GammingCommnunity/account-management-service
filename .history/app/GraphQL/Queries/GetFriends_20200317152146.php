<?php

namespace App\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use App\AccountRelationship;
use App\Enums\DbEnums\AccountRelationshipType;
use App\GraphQL\Entities\Result\FriendResult;

class GetFriends
{
	/**
	 * Return a value for the field.
	 *
	 * @param  null  $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
	 * @param  mixed[]  $args The arguments that were passed into the field.
	 * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context Arbitrary data that is shared between all fields of a single query.
	 * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
	 * @return array FriendResult[]
	 */
	public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
	{
		$result = [];

		if ($rootValue['verified_account']) {
			$currentAccount = $rootValue['verified_account'];

			if ($currentAccount) {
				$relationships = AccountRelationship::where('relationship_type', AccountRelationshipType::FRIEND)->where(function ($query) use ($currentAccount){
					return $query->where('sender_account_id', $currentAccount->id)->orWhere('receiver_account_id', $currentAccount->id);
				})->get(['sender_account_id', 'receiver_account_id', 'updated_at']);
				
				$result = $this->getFriendsList($currentAccount->id, $relationships);
			}
		}

		return $result;
	}

	protected function getFriendsList(int $id, $relationships): array{
		$result = [];

		foreach ($relationships as $relationship) {
			$friendResult = new FriendResult(null, $relationship->updated_at);

			if($id === $relationship->sender_account_id){
				$friendResult->account = $relationship->receiver;

				array_push($result, $relationship->receiver);
			} else {
				$friendResult->account = $relationship->sender;
				array_push($result, $relationship->sender);
			}
		}

		return $result;
	}
}