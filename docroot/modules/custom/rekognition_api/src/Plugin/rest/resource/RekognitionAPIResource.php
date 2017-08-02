<?php

namespace Drupal\rekognition_api\Plugin\rest\resource;

use Drupal\Core\Entity\Entity;
use Drupal\media_entity\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a Rekognition API Endpoint Resource
 *
 * @RestResource(
 *   id = "rekognition_api_resource",
 *   label = @Translation("Rekognition API Resource"),
 *   uri_paths = {
 *     "canonical" = "/rekognition_api/objects"
 *   }
 * )
 */
class RekognitionAPIResource extends ResourceBase {

  /**
   * Responds to entity GET requests.
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    $response = ['message' => 'Hello, this is a rest service'];
    return new ResourceResponse($response);
  }

  public function put($body) {
    $jsonBody = json_encode($body);
    \Drupal::logger('rekognition_api')->notice("PUT body is:\n{$jsonBody}");

    // Find the image!
    $uri = "s3://{$body['Name']}";

    $files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties(['uri' => $uri]);
    if (empty($files)) {
      throw new \Exception("File not found in database: $uri");
    }

    $file = reset($files);
    $fid = $file->fid->value;

    $query = \Drupal::entityQuery('media');
    $query->condition('bundle', 'image')
      ->condition('field_image', $fid);

    $result = $query->execute();
    $media_id = reset($result);
    $media = Media::load($media_id);

    \Drupal::logger('rekognition_api')->notice("media found: {$media->id}");

    if (!empty($body['Labels'])) {
      $tids = $this->findOrCreateTerms($body['Labels']);
      $jsonTids = json_encode($body);
      \Drupal::logger('rekognition_api')->notice("adding terms: {$jsonTids}");

      $media->set('field_label', $tids);
      $media->save();
    }

    if (!empty($body['Faces'])) {
      $faceNodeIds = [];
      foreach ($body['Faces'] as $faceInfo) {
        if (empty($faceInfo['Face']['FaceId'])) {
          throw new \Exception("FaceId is null");
        }
        $faceId = $faceInfo['Face']['FaceId'];
        $similarFaceIds = $this->extractSimilarFaceIds($faceInfo['FaceMatches']);
        $nameNodeId = $this->findOrCreateName($similarFaceIds);
        $faceNodeIds[] = $this->createFace($faceId, $nameNodeId);
      }
      $jsonFaces = json_encode($faceNodeIds);
      \Drupal::logger('rekognition_api')->notice("adding faces: {$jsonFaces}");

      $media->set('field_face', $faceNodeIds);
      $media->save();
    }

    $jsonMedia = json_encode($media);
    \Drupal::logger('rekognition_api')->notice("media updated: {$jsonMedia}");
    $response = ['media' => $media];
    return new ResourceResponse($response);
  }

  private function createFace($faceId, $nameNodeId)
  {
    $node = Node::create([
      'type'   => 'face_uuid',
      'title'       => $faceId,
      'field_name' => [
        'target_id' => $nameNodeId
      ]
    ]);
    $node->save();
//    $jsonNode = var_export($node, true);
//    \Drupal::logger('rekognition_api')->notice("face created: {$jsonNode}");
//    return $node->id();
    return $node->id();
  }

  private function extractSimilarFaceIds($faceMatches)
  {
    return array_map(function ($match) {
      return $match['Face']['FaceId'];
    }, $faceMatches);
  }

  private function findOrCreateName($similarFaceIds)
  {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'face_uuid')
      ->condition('title', $similarFaceIds, 'IN');

    $result = $query->execute();
    if (empty($result)) {
      $rand = rand();
      $node = Node::create([
        'type'        => 'name',
        'title'       => "Unknown Person $rand",
      ]);
      $node->save();
      return $node->id();
    }
    $faceNodeId = reset($result);
    $faceNode = Node::load($faceNodeId);

//    $faceJson = var_export($faceNode, true);
//    \Drupal::logger('rekognition_api')->notice("found face node: $faceJson");
    \Drupal::logger('rekognition_api')->notice("found name node: {$faceNode->field_name[0]->target_id}");

    return $faceNode->field_name[0]->target_id;
  }

  private function findOrCreateTerms($labels)
  {
    $tids = [];
    foreach ($labels as $label) {
      $query = \Drupal::entityQuery('taxonomy_term');
      $query->condition('vid', "labels");
      $query->condition('name', $label['Name']);
      $labelTids = $query->execute();
      if (!empty($labelTids)) {
        $tids = $tids + $labelTids;
        continue;
      }

      $term = \Drupal\taxonomy\Entity\Term::create([
        'vid' => 'labels',
        'name' => $label['Name'],
        'weight' => 0,
        'parent' => [],
      ]);
      $term->save();
      $tids[] = $term->id();
    }
    return $tids;
  }
}
