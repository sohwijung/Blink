<?php
include("includes/init.php");
$title = "Gallery";
$messages = array();

const MAX_FILE_SIZE = 1000000;
//code for upload is complete, but i'm not sure why it's not working
if (isset($_POST["submit_upload"])) {
  $upload_name = trim(filter_input(INPUT_POST, 'upload_image_name', FILTER_SANITIZE_STRING));
  $upload_source = trim(filter_input(INPUT_POST, 'upload_image_source', FILTER_SANITIZE_STRING));
  $upload = $_FILES["my_upload"];

  if ($upload_name == "") {
    array_push($messages, "Please enter a value for name.");
  } else if ($upload_source == "") {
    array_push($messages, "Please enter a source for the image.");
  } else if (empty($upload['name']))  {
    array_push($messages, "Please choose an image to upload.");
  } else if($upload["error"] == UPLOAD_ERR_OK) {
    $path = $upload["name"];
    $basename = basename($path);
    $upload_ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
    $sql = "INSERT INTO images (file_name, file_ext, file_source) VALUES (:filename, :fileext, :filesource);";
    $params = array(":filename" => $upload_name, ":fileext" => $upload_ext, ":filesource" => $upload_source);
    $result = exec_sql_query($db, $sql, $params);

    if ($result) {
      $id = $db -> lastInsertId("id");
      settype($id, "string");
      $upload_path = "uploads/images/".$id.".".$upload_ext;
      move_uploaded_file($_FILES["my_upload"]["tmp_name"], $upload_path);
      array_push($messages, "Successfully uploaded image!");
    }
  } else {
    array_push($messages, "Please choose an image of size less than 1MB.");
  }
}

if (isset($_GET["filter_submit"])) {
  $activeFilter = true;
  $selectedTag = filter_input(INPUT_GET, 'tagFilter', FILTER_SANITIZE_STRING);
  $tagIdSql = "SELECT id FROM tags WHERE tag = :selectedTag;";
  $params = array(
      ':selectedTag' => $selectedTag
  );
  $filterTagId = exec_sql_query($db, $tagIdSql, $params)->fetchAll();
  $tagId = htmlspecialchars($filterTagId[0][0]);
} else {
  $activeFilter = false;
}

if (isset($_POST["delete"])) {
  $db->beginTransaction();

  $imageId = filter_input(INPUT_POST, 'imageId', FILTER_SANITIZE_STRING);
  $path = filter_input(INPUT_POST, 'path', FILTER_SANITIZE_STRING);

  $deleteImageSql = "DELETE FROM images WHERE id = :imageId;";
  try {
    $result = exec_sql_query($db, $deleteImageSql, array(':imageId' => $imageId));
  } catch (Exception $exception){}

  $selectRemoveLink = "SELECT id FROM links WHERE image_id = :imageId;";
  try { $execRemoveLink = exec_sql_query($db, $selectRemoveLink, array(':imageId' => $imageId))->fetchAll();
  } catch (Exception $exception){}

  foreach($execRemoveLink as $id){
    $deleteLinkSql = "DELETE FROM links WHERE id = :id;";
    $params = array(':id' => htmlspecialchars($id[0]));
    try {
      exec_sql_query($db, $deleteLinkSql, $params);
      array_push($messages, "Successfully deleted image.");
    } catch(Exception $exception){
      array_push($messages, "Failed to delete image.");
    }
  }
  unlink($path);
  $db->commit();
}
?>

<!DOCTYPE html>
<html lang="en">

<?php include("includes/head.php"); ?>

<body>
  <?php include("includes/header.php");
    echo filesize($upload);
  ?>

  <?php
    function list_tags($oneTag) { ?>
    <option value=<?php echo $oneTag ?>> <?php echo $oneTag ?> </option>
  <?php } ?>

  <div class="gallery_link"><a href = "gallery.php">Gallery</a></div>

  <div class="feedback_msg">
    <?php
      foreach ($messages as $message) {
        echo "<p>" . htmlspecialchars($message) . "</p>\n";
      }
    ?>
  </div>

  <div class = "upload_filter">
    <form id="tagFilter" method="GET" enctype="multipart/form-data">
      <label for="tagFilter">Filter by tag:</label>
      <?php
        global $db;
        $tagSql = "SELECT DISTINCT tag FROM tags INNER JOIN links ON tags.id = links.tag_id;";
        $executeTags = exec_sql_query($db, $tagSql, array());
        if ($executeTags) {
          $tags = $executeTags->fetchAll();
      ?>

      <select name="tagFilter">
        <?php
          foreach ($tags as $tag) { list_tags(htmlspecialchars($tag[0])); }
        }
        ?>
      </select>

      <button name="filter_submit" class = "filter_submit" >Filter</button>
    </form>
  </div>

  <?php
  if ($activeFilter) { ?>
    <div class="filter"><h3>Current filter: <?php echo htmlspecialchars($selectedTag); ?> </h3></div>
  <?php }?>

  <?php
  function display_gallery($images) {
    $path = "uploads/images/" . htmlspecialchars($images['id']) . "." . htmlspecialchars($images['file_ext']);
    ?>

    <a href="<?php echo 'image.php?' . http_build_query(array('imageId' => htmlspecialchars($images['id']))); ?>">
      <img src=<?php echo $path ?>>
    </a>
  <?php } ?>

  <ul class = "gallery">

    <?php
    $sqlGallery = "SELECT * FROM images;";
    $params = array();
    if ($activeFilter) {
      $sqlGallery = "SELECT DISTINCT images.* FROM images INNER JOIN links ON images.id = links.image_id INNER JOIN tags ON tags.id = links.tag_id WHERE tags.id = :tagFilter;";
      $params = array(':tagFilter' => $tagId);
    }
    $images = exec_sql_query($db, $sqlGallery, $params)->fetchAll(PDO::FETCH_ASSOC);

    if ($images) {
      foreach ($images as $image) {
        display_gallery($image);
      }
    } ?>

  </ul>

  <h3 class="uploadHeading"> Upload an image </h3>
  <form method="post" enctype="multipart/form-data" id="uploadFile" action="gallery.php" class="uploadForm">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE; ?>"/>

    <label for="upload_image_name">Name: </label>
    <input type="textbox" name="upload_image_name"></input>

    <label for="upload_image_source">Photo by: </label>
    <input type="textbox" name="upload_image_source"></input>

    <label for="my_upload">Image:</label>
    <input type="file" id="my_upload" name="my_upload" accept="image/jpeg, image/png"/>
    <div></div>
    <button name="submit_upload" class="upload">
      <!-- Image Source: https://icons8.com/icon/97641/upload -->
      <div><img alt="uploadIcon" src="images/uploadIcon.png" /></div><div>Upload</div>
    </button>

  </form>
  <p class="source">Image Source: <a href="https://icons8.com/icon/97641/upload">Upload icon by Icons8</a></p>


<?php include("includes/footer.php"); ?>
</body>
</html>
