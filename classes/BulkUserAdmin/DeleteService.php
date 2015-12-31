<?php
namespace BulkUserAdmin;

class DeleteService {
	private $queue;
	private $entities;
	const PENDING_DELETE_MD = 'bulk_user_admin_delete_queued';

	private function __construct(\Elgg\Queue\Queue $queue, \Elgg\Database\EntityTable $entities) {
		$this->queue = $queue;
		$this->entities = $entities;
	}

	public function process($end_ts) {
		$ia = elgg_set_ignore_access(true);
		$show_hidden = access_show_hidden_entities(true);

		while (time() < $end_ts) {
			$user = $this->queue->dequeue();

			// end of queue
			if ($user === null) {
				return;
			}

			// don't break on bad data
			if (!$user instanceof \ElggUser) {
				echo "Guid: $user->guid. Expected ElggUser, got $user->type<br />\n";
				return false;
			}

			echo "Deleting user $user->username ($user->email, $user->guid)<br />\n";

			$user->delete();
		}

		elgg_set_ignore_access($ia);
		access_show_hidden_entities($show_hidden);
	}

	public function enqueue(\ElggUser $user) {
		if (!$user instanceof \ElggUser) {
			throw new \UnexpectedValueException("DeleteService->enqueue() expects an ElggUser object");
		}

		$user->{self::PENDING_DELETE_MD} = true;
		return $this->queue->enqueue($user);
	}

	public static function getService() {
		$db = _elgg_services()->db;
		$queue = new \Elgg\Queue\DatabaseQueue('bulk_user_admin', $db);

		$entities = _elgg_services()->entityTable;

		return new self($queue, $entities);
	}
}