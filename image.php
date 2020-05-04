<?php include("includes/init.php");
$title = "Image";
$messages = array();

$imageId = filter_input(INPUT_GET, 'imageId', FILTER_SANITIZE_STRING);
$getImageIdSql = "SELECT * FROM images WHERE id = :imageId;";
$params = array(
    ':imageId' => $imageId
);
$execEachImageId = (exec_sql_query($db, $getImageIdSql, $params)->fetchAll())[0];

// Add Existing Tag
if (isset($_POST["submit_existing"])) {
    $selectExistingTag = filter_input(INPUT_POST, 'existingTag', FILTER_SANITIZE_STRING);
    $getExistingTagIdSql = "SELECT id FROM tags WHERE tag = :selectExistingTag;";
    $param = array(':selectExistingTag' => $selectExistingTag);
    $execSelectExistingTag = exec_sql_query($db, $getExistingTagIdSql, $param)->fetchAll();

    $addTagId = htmlspecialchars($execSelectExistingTag[0][0]);
    $addExistingLinkSql = "INSERT INTO links (image_id, tag_id) VALUES (:imageId, :addTagId);";
    $params = array(
        ':imageId' => $imageId,
        ':addTagId' => $addTagId
    );

    //Checks for duplicate links
    try {
        $result = exec_sql_query($db, $addExistingLinkSql, $params)->fetchAll();
        array_push($messages, "Successfully added existing tag!");
    } catch (Exception $exception){
        array_push($messages, "Failed to add existing tag.");
    }
}

//Add New Tag
if (isset($_POST['submit_new'])) {
    $db->beginTransaction();

    $newTag = filter_input(INPUT_POST, 'newTag', FILTER_SANITIZE_STRING);
    if (trim($newTag) == "") {
        array_push($messages, "Please enter a value for add new tag.");
    } else {
        $newTagSql = "INSERT INTO tags (tag) VALUES (:newTag);";
        $params = array(':newTag' => $newTag);
        //Checks for duplicate tags
        try {
            $result = exec_sql_query($db, $newTagSql, $params);
        } catch (Exception $exception) { }

        $getNewTagIdSql = "SELECT id FROM tags WHERE tag = :newTag;";
        $execGetNewTagId = exec_sql_query($db, $getNewTagIdSql, $params)->fetchAll();
        $addTagId = htmlspecialchars($execGetNewTagId[0][0]);

        $addNewLinkSql = "INSERT INTO links (image_id, tag_id) VALUES (:imageId, :addTagId);";
        $params = array(
            ':imageId' => $imageId,
            ':addTagId' => $addTagId
        );
        //Checks for duplicate links
        try{
            $result = exec_sql_query($db, $addNewLinkSql, $params)->fetchAll();
            array_push($messages, "Successfully added new tag!");
        } catch(Exception $exception){
            array_push($messages, "Failed to add new tag.");
        }
    }

    $db->commit();
}

//Delete tag
if (isset($_POST["submit_delete_tag"])) {
    $deleteTag = filter_input(INPUT_POST, 'deleteTag', FILTER_SANITIZE_STRING);
    $getDeleteTagIdSql = "SELECT id FROM tags WHERE tag = :deleteTag;";
    $param = array(':deleteTag' => $deleteTag);
    $execDeleteTagId = exec_sql_query($db, $getDeleteTagIdSql, $param)->fetchAll();
    $deleteTagId = htmlspecialchars($execDeleteTagId[0][0]);

    $deleteLinkSql = "DELETE FROM links WHERE image_id = :imageId AND tag_id = :deleteTagId;";
    $params = array(
        ':imageId' => $imageId,
        ':deleteTagId' => $deleteTagId
    );
    try{
        $result = exec_sql_query($db, $deleteLinkSql, $params)->fetchAll();
        array_push($messages, "Successfully deleted tag!");
    } catch(Exception $exception){
        array_push($messages, "Failed to delete tag.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include("includes/head.php"); ?>

<body>
    <?php include("includes/header.php"); ?>

    <div class = "image_flex">
    <div class = "eachimage_flex">
        <?php $path = "uploads/images/" . $execEachImageId["id"] . "." . htmlspecialchars($execEachImageId['file_ext']); ?>
        <img class = "individ_img" src=<?php echo $path ?>>
    </div>

    <?php
    function display_tag($thisImg) {
        $getTagSql = "SELECT DISTINCT tag FROM tags INNER JOIN links ON tags.id = links.tag_id INNER JOIN images ON links.image_id = images.id WHERE images.id = :thisImg;";
        $params = array(':thisImg' => $thisImg);
        global $db;
        $tags = exec_sql_query($db, $getTagSql, $params)->fetchAll();

        foreach ($tags as $tag) {
            echo ("<li>" . htmlspecialchars($tag[0]) . "</li>");
        }
    } ?>


    <div class = "eachimage_flex">
        <div class = "text">
            <div class="imageName"><h2><?php echo $execEachImageId["file_name"]?></h2></div>
            <div class="imageSource"><p>Photo by: <?php echo $execEachImageId["file_source"]?></p></div>
            <div class="feedback_msg_img">
                <?php
                foreach ($messages as $message) {
                    echo "<p>" . htmlspecialchars($message) . "</p>\n";
                }
                ?>
            </div>
            <h3 class="tagsHeader">Tags</h3>
            <ul class="tags">
                <?php display_tag($imageId); ?>
            </ul>
        </div>

        <?php
            function list_tags($tag)
                { ?>
                <option value=<?php echo $tag ?>> <?php echo $tag ?> </option>
            <?php
        } ?>


        <form id="addExistingTag" method="POST" enctype="multipart/form-data">
        <div class="form_element"><label for="existingTag">Add existing tag: </label></div>
        <div class="form_element">
            <?php
            global $db;
            $tagSql = "SELECT DISTINCT tag FROM tags WHERE tags.id NOT IN (SELECT DISTINCT tag_id FROM links INNER JOIN tags ON tags.id = links.tag_id INNER JOIN images ON links.image_id = $imageId);";
            $executeTags = exec_sql_query($db, $tagSql, array());
            if ($executeTags) {
                $tags = $executeTags->fetchAll();
            ?>
            <select name="existingTag">
                <?php
                foreach ($tags as $tag) {
                    list_tags(htmlspecialchars($tag[0]));
                }}
                ?>
            </select>
            <button name="submit_existing" class="icon">+</button>
        </div>
        </form>

        <form id="submit_new_tag" method="POST" enctype="multipart/form-data">
        <div class="form_element">
            <label for="newTag">Add a new tag: </label>
        </div>
        <div class="form_element">
            <input type="text" name="newTag" class="newTag"></input>
            <button name="submit_new" class="icon">
                +
            </button>
        </div>
        </form>

        <form id="deleteExistingTag" method="POST" enctype="multipart/form-data">
        <div class="form_element"><label for="deleteTag">Delete existing tag: </label></div>
        <div class="form_element">
            <?php
            global $db;

            $tagSql = "SELECT DISTINCT tag FROM tags INNER JOIN links ON tags.id = links.tag_id INNER JOIN images ON links.image_id = images.id WHERE images.id = $imageId;";
            $executeTags = exec_sql_query($db, $tagSql, array());
            if ($executeTags) {
                $tags = $executeTags->fetchAll();
            ?>
            <select name="deleteTag">
                <?php
                foreach ($tags as $tag) {
                    list_tags(htmlspecialchars($tag[0]));
                }}
                ?>
            </select>
            <button name="submit_delete_tag" class="icon">-</button>
        </div>
        </form>


        <div class="goback"><a href="gallery.php">Go back to gallery</a></div>

        <form id="deleteImage" action="gallery.php" method="POST" enctype="multipart/form-data" class="deleteImage">
            <div>
            <input type="hidden" name="imageId" value="<?php echo ($imageId);?>">
            <input type="hidden" name="path" value="<?php echo ($path);?>">
            <button name="delete" class="delete">
                <!-- Image Source: https://icons8.com/icon/11705/trash-can -->
                <div><img alt="deleteIcon" src="images/trash.png" /></div><div>Delete Image</div>
            </button>
            </div>
            <div>
            <p class="deleteSource">Image Source: <a href="https://icons8.com/icon/11705/trash-can">Trash Can icon by Icons8</a></p>
            </div>
        </form>


    </div>
</div>



  <?php include("includes/footer.php"); ?>


</body>

</html>
