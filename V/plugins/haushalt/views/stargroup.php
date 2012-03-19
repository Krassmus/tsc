<?php

$id = "stargroup_".md5(uniqid());

?>
<div id="<?= $id ?>" style="width: <?= $width + 10 ?>px; height: <?= ($width * 3 / 5) + 10 ?>px; margin-left: auto; margin-right: auto; background-color: #111111; padding: 5px; border-radius: 10px; -moz-border-radius: 10px; border: thin solid #aaaaaa;"></div>
<script>
$(function () {
    var scale = 70;
    var width = <?= $width ?>;
    var height = <?= $width * 3 / 5 ?>;
    var zoom = <?= 80 * $viewradius * $zoom ?>;
    var camera = new THREE.Camera(90, width / height, 1, 10000 );
    camera.position.z = zoom;

    var mouseX = 0, mouseY = 0;
    var windowHalfX = window.innerWidth / 2;
    var windowHalfY = window.innerHeight / 2;

    var scene = new THREE.Scene();
    var viewpoint = <?= json_encode($viewcenter) ?>;
    
    var systems = <?= json_encode($systems) ?>;

    /*var material = new THREE.ParticleBasicMaterial( { map: new THREE.Texture( generateSprite() ), blending: THREE.AdditiveBlending } );*/

    $.each(systems, function (index, system) {
        var width = (system.size + 7);
        var opacities = [1, 0.4, 0.1, 0.05];
        var scales = [1/3, 3/5, 1, 6/5];
        /* ein Stern */
        $.each([1,2,3,4], function (index, value) {
            var particle = new THREE.Particle( new THREE.ParticleCircleMaterial( { color: index === 0 ? 0xffffff : parseInt(system.color, 16), opacity: opacities[index] } ) );
            particle.position.x = (system.x - viewpoint.x) * scale;
            particle.position.y = (system.y - viewpoint.y) * scale;
            particle.position.z = (system.z -viewpoint.z) * scale;
            particle.scale.x = particle.scale.y = width * scales[index];
            scene.addObject( particle );
        });
        $.each(system.connections, function (index2, neighbor) {
            /* Line */
            var material = new THREE.LineBasicMaterial( { color: 0xffffff, opacity: 0.5, linewidth: 3 } );
            var geometry = new THREE.Geometry();
            geometry.vertices.push( new THREE.Vertex( new THREE.Vector3( system.x * scale, system.y * scale, system.y * scale ) ) );
            geometry.vertices.push( new THREE.Vertex( new THREE.Vector3( systems[neighbor].x * scale, systems[neighbor].y * scale, systems[neighbor].z * scale ) ) );
            var line = new THREE.Line( geometry, material );
            line.position.x = 0;
            line.position.y = 0;
            line.position.z = 0;
            scene.addObject( line );
        });
    });

    var renderer = new THREE.CanvasRenderer();
    renderer.setSize( width, height );

    $('#<?= $id ?>').append( renderer.domElement );

    var animate = function () {
        /* Include examples/js/RequestAnimationFrame.js for cross-browser compatibility.*/
        requestAnimationFrame( animate );
        render();
    };

    var starttime = new Date();
    var render = function () {
        if (false) {
            camera.position.x += ( mouseX - camera.position.x ) * 0.05;
            camera.position.y += ( - mouseY - camera.position.y ) * 0.05;
        } else {
            var units_per_circle = 6000;
            var current_time = new Date();
            camera.position.x = Math.sin((current_time - starttime) / units_per_circle) * 1000;
            camera.position.z = Math.cos((current_time - starttime) / units_per_circle) * 1000;
        }

        renderer.render( scene, camera );
    };
    animate(); 
    
    
    var onDocumentMouseMove = function (event) {
        mouseX = event.clientX - windowHalfX;
        mouseY = event.clientY - windowHalfY;
    };
    document.addEventListener( 'mousemove', onDocumentMouseMove, false );
    
    /*requestAnimationFrame( animate );*/
    /*renderer.render( scene, camera );*/
});
</script>