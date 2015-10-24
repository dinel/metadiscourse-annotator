<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WebAnnotatorController extends Controller
{
    public function indexAction() {
        $text = <<< EOT
Part of the M6 in Staffordshire which was <span class="meta-marker" id="MD1">closed</span> when heat from a burning lorry <span class="meta-marker" id="MD2">melted</span> the 
    road surface, has reopened, police said.
The southbound section between junction 13 (Stafford south) and junction 12 (Cannock) was closed following the accident about 01:30 BST.
Traffic officers tweeted a picture of the HGV surrounded by flames which had spread across the carriageway.
Five miles of queues were reported and some people were stuck for six hours.
Long delays were also expected on all surrounding routes, police said earlier.
Earlier Highways England said the "intensity of the fire has caused extensive damage to the carriageway and resurfacing is required".

Part of the M6 in Staffordshire which was <span class="meta-marker" id="MD1">closed</span> when heat from a burning lorry <span class="meta-marker" id="MD2">melted</span> the 
    road surface, has reopened, police said.
The southbound section between junction 13 (Stafford south) and junction 12 (Cannock) was closed following the accident about 01:30 BST.
Traffic officers tweeted a picture of the HGV surrounded by flames which had spread across the carriageway.
Five miles of queues were reported and some people were stuck for six hours.
Long delays were also expected on all surrounding routes, police said earlier.
Earlier Highways England said the "intensity of the fire has caused extensive damage to the carriageway and resurfacing is required".
        
   Part of the M6 in Staffordshire which was <span class="meta-marker" id="MD1">closed</span> when heat from a burning lorry <span class="meta-marker" id="MD2">melted</span> the 
    road surface, has reopened, police said.
The southbound section between junction 13 (Stafford south) and junction 12 (Cannock) was closed following the accident about 01:30 BST.
Traffic officers tweeted a picture of the HGV surrounded by flames which had spread across the carriageway.
Five miles of queues were reported and some people were stuck for six hours.
Long delays were also expected on all surrounding routes, police said earlier.
Earlier Highways England said the "intensity of the fire has caused extensive damage to the carriageway and resurfacing is required".Part of the M6 in Staffordshire which was <span class="meta-marker" id="MD1">closed</span> when heat from a burning lorry <span class="meta-marker" id="MD2">melted</span> the 
    road surface, has reopened, police said.
The southbound section between junction 13 (Stafford south) and junction 12 (Cannock) was closed following the accident about 01:30 BST.
Traffic officers tweeted a picture of the HGV surrounded by flames which had spread across the carriageway.
Five miles of queues were reported and some people were stuck for six hours.
Long delays were also expected on all surrounding routes, police said earlier.
Earlier Highways England said the "intensity of the fire has caused extensive damage to the carriageway and resurfacing is required".Part of the M6 in Staffordshire which was <span class="meta-marker" id="MD1">closed</span> when heat from a burning lorry <span class="meta-marker" id="MD2">melted</span> the 
    road surface, has reopened, police said.
The southbound section between junction 13 (Stafford south) and junction 12 (Cannock) was closed following the accident about 01:30 BST.
Traffic officers tweeted a picture of the HGV surrounded by flames which had spread across the carriageway.
Five miles of queues were reported and some people were stuck for six hours.
Long delays were also expected on all surrounding routes, police said earlier.
Earlier Highways England said the "intensity of the fire has caused extensive damage to the carriageway and resurfacing is required".Part of the M6 in Staffordshire which was <span class="meta-marker" id="MD1">closed</span> when heat from a burning lorry <span class="meta-marker" id="MD2">melted</span> the 
    road surface, has reopened, police said.
The southbound section between junction 13 (Stafford south) and junction 12 (Cannock) was closed following the accident about 01:30 BST.
Traffic officers tweeted a picture of the HGV surrounded by flames which had spread across the carriageway.
Five miles of queues were reported and some people were stuck for six hours.
Long delays were also expected on all surrounding routes, police said earlier.
Earlier Highways England said the "intensity of the fire has caused extensive damage to the carriageway and resurfacing is required".Part of the M6 in Staffordshire which was <span class="meta-marker" id="MD1">closed</span> when heat from a burning lorry <span class="meta-marker" id="MD2">melted</span> the 
    road surface, has reopened, police said.
The southbound section between junction 13 (Stafford south) and junction 12 (Cannock) was closed following the accident about 01:30 BST.
Traffic officers tweeted a picture of the HGV surrounded by flames which had spread across the carriageway.
Five miles of queues were reported and some people were stuck for six hours.
Long delays were also expected on all surrounding routes, police said earlier.
Earlier Highways England said the "intensity of the fire has caused extensive damage to the carriageway and resurfacing is required".
                
                
EOT;
        
        $params = array("text" => $text);
        
        return $this->render('DinelAnnotatorBundle:WebAnnotator:index.html.twig', 
                $params);
    }
}
