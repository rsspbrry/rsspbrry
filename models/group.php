<?php

namespace Model\Group;

use PicoDb\Database;

/**
 * Get all groups
 *
 * @return array
 */
function get_all()
{
    return Database::getInstance('db')
            ->table('groups')
            ->orderBy('title')
            ->findAll();
}

/**
 * Get assoc array of group ids with assigned feeds ids
 *
 * @return array
 */
function get_map()
{
    $result = Database::getInstance('db')
            ->table('feeds_groups')
            ->findAll();

    // TODO: add PDO::FETCH_COLUMN|PDO::FETCH_GROUP to picodb and use it instead
    // of the following lines
    $map = array();

    foreach ($result as $row) {
        $group_id = $row['group_id'];
        $feed_id = $row['feed_id'];

        if (isset($map[$group_id])) {
            $map[$group_id][] = $feed_id;
        }
        else {
            $map[$group_id] = array($feed_id);
        }
    }

    return $map;
}

/**
 * Get assoc array of feeds ids with assigned groups ids
 *
 * @return array
 */
function get_feeds_map()
{
    $result = Database::getInstance('db')
            ->table('feeds_groups')
            ->findAll();
    $map = array();
    foreach ($result as $row) {
        $map[$row['feed_id']][] = $row['group_id'];
    }

    return $map;
}

/**
 * Get all groups assigned to feed
 *
 * @param integer $feed_id id of the feed
 * @return array
 */
function get_feed_group_ids($feed_id)
{
    return Database::getInstance('db')
            ->table('groups')
            ->join('feeds_groups', 'group_id', 'id')
            ->eq('feed_id', $feed_id)
            ->findAllByColumn('id');
}

/**
 * Get the id of a group
 *
 * @param string $title group name
 * @return mixed group id or false if not found
 */
function get_group_id($title)
{
    return Database::getInstance('db')
            ->table('groups')
            ->eq('title', $title)
            ->findOneColumn('id');
}

/**
 * Get all feed ids assigned to a group
 *
 * @param integer $group_id
 * @return array
 */
function get_feeds_by_group($group_id)
{
    return Database::getInstance('db')
            ->table('feeds_groups')
            ->eq('group_id', $group_id)
            ->findAllByColumn('feed_id');
}

/**
 * Add a group to the Database
 *
 * Returns either the id of the new group or the id of an existing group with
 * the same name
 *
 * @param string $title group name
 * @return mixed id of the created group or false on error
 */
function create($title)
{
    $data = array('title' => $title);

    // check if the group already exists
    $group_id = get_group_id($title);

    // create group if missing
    if ($group_id === false) {
       Database::getInstance('db')
                ->table('groups')
                ->insert($data);

        $group_id = get_group_id($title);
    }

    return $group_id;
}

/**
 * Add groups to feed
 *
 * @param integer $feed_id feed id
 * @param array $group_ids array of group ids
 * @return boolean true on success, false on error
 */
function add($feed_id, $group_ids)
{
    foreach ($group_ids as $group_id){
        $data = array('feed_id' => $feed_id, 'group_id' => $group_id);

        $result = Database::getInstance('db')
                ->table('feeds_groups')
                ->insert($data);

        if ($result === false) {
            return false;
        }
    }

    return true;
}

/**
 * Remove groups from feed
 *
 * @param integer $feed_id id of the feed
 * @param array $group_ids array of group ids
 * @return boolean true on success, false on error
 */
function remove($feed_id, $group_ids)
{
    $result = Database::getInstance('db')
            ->table('feeds_groups')
            ->eq('feed_id', $feed_id)
            ->in('group_id', $group_ids)
            ->remove();

    // remove empty groups
    if ($result) {
        purge_groups();
    }

    return $result;
}

/**
 * Remove all groups from feed
 *
 * @param integer $feed_id id of the feed
 * @return boolean true on success, false on error
 */
function remove_all($feed_id)
{
    $result = Database::getInstance('db')
            ->table('feeds_groups')
            ->eq('feed_id', $feed_id)
            ->remove();

    // remove empty groups
    if ($result) {
        purge_groups();
    }

    return $result;
}

/**
 * Purge orphaned groups from database
 */
function purge_groups()
{
    $groups = Database::getInstance('db')
                ->table('groups')
                ->join('feeds_groups', 'group_id', 'id')
                ->isnull('feed_id')
                ->findAllByColumn('id');

    if (! empty($groups)) {
        Database::getInstance('db')
            ->table('groups')
            ->in('id', $groups)
            ->remove();
    }
}

/**
 * Update feed group associations
 *
 * @param integer $feed_id id of the feed to update
 * @param array $group_ids valid groups ids for feed
 * @param string $create_group group to create and assign to feed
 * @return boolean
 */
function update_feed_groups($feed_id, $group_ids, $create_group = '')
{
    if ($create_group !== '') {
        $id = create($create_group);

        if ($id === false) {
            return false;
        }

        if (! in_array($id, $group_ids)) {
            $group_ids[] = $id;
        }
    }

    $assigned = get_feed_group_ids($feed_id);
    $superfluous = array_diff($assigned, $group_ids);
    $missing = array_diff($group_ids, $assigned);

    // remove no longer assigned groups from feed
    if (! empty($superfluous) && ! remove($feed_id, $superfluous)) {
        return false;
    }

    // add requested groups to feed
    if (! empty($missing) && ! add($feed_id, $missing)) {
        return false;
    }

    return true;
}
