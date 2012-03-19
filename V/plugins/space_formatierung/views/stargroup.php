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

    var program = function ( context ) {
        context.beginPath();
        context.arc( 0, 0, 1, 0, PI2, true );
        context.closePath();
        context.fill();
    }

    /*var material = new THREE.ParticleBasicMaterial( { map: new THREE.Texture( generateSprite() ), blending: THREE.AdditiveBlending } );*/

    $.each(systems, function (index, system) {
        var width = (system.size + 7);
        var opacities = [1, 0.4, 0.1, 0.05];
        var scales = [1/3, 3/5, 1, 6/5];
        /* ein Stern */
        $.each([1,2,3,4], function (index, value) {
            /*var material = new THREE.ParticleCircleMaterial({
                color: index === 0 ? 0xffffff : parseInt(system.color, 16),
                opacity: opacities[index]
            });*/
            group = new THREE.Object3D();
            scene.addObject(group);
            var material = new THREE.ParticleCanvasMaterial({
                color: index === 0 ? 0xffffff : parseInt(system.color, 16),
                program: program,
                opacity: opacities[index]
            });
            var particle = new THREE.Particle(material);
            particle.position.x = (system.x - viewpoint.x) * scale;
            particle.position.y = (system.y - viewpoint.y) * scale;
            particle.position.z = (system.z -viewpoint.z) * scale;
            particle.scale.x = particle.scale.y = width * scales[index];
            group.addChild(particle);
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
    document.addEventListener('mousemove', onDocumentMouseMove, false);
    
    /*requestAnimationFrame( animate );*/
    /*renderer.render( scene, camera );*/
});
</script>


<script type="text/javascript">

			var container, stats;
			var camera, scene, renderer, group, particle;
			var mouseX = 0, mouseY = 0;

			var windowHalfX = window.innerWidth / 2;
			var windowHalfY = window.innerHeight / 2;

			init();
			animate();

			function init() {

				container = document.createElement( 'div' );
				document.body.appendChild( container );

				camera = new THREE.Camera( 75, window.innerWidth / window.innerHeight, 1, 3000 );
				camera.position.z = 1000;

				scene = new THREE.Scene();

				var PI2 = Math.PI * 2;
				var program = function ( context ) {

					context.beginPath();
					context.arc( 0, 0, 1, 0, PI2, true );
					context.closePath();
					context.fill();

				}

				group = new THREE.Object3D();
				scene.addObject( group );

				for ( var i = 0; i < 1000; i++ ) {

					particle = new THREE.Particle( new THREE.ParticleCanvasMaterial( { color: Math.random() * 0x808008 + 0x808080, program: program } ) );
					particle.position.x = Math.random() * 2000 - 1000;
					particle.position.y = Math.random() * 2000 - 1000;
					particle.position.z = Math.random() * 2000 - 1000;
					particle.scale.x = particle.scale.y = Math.random() * 10 + 5;
					group.addChild( particle );
				}

				renderer = new THREE.CanvasRenderer();
				renderer.setSize( window.innerWidth, window.innerHeight );
				container.appendChild( renderer.domElement );

				stats = new Stats();
				stats.domElement.style.position = 'absolute';
				stats.domElement.style.top = '0px';
				container.appendChild( stats.domElement );

				document.addEventListener( 'mousemove', onDocumentMouseMove, false );
				document.addEventListener( 'touchstart', onDocumentTouchStart, false );
				document.addEventListener( 'touchmove', onDocumentTouchMove, false );
			}

			//

			function onDocumentMouseMove( event ) {

				mouseX = event.clientX - windowHalfX;
				mouseY = event.clientY - windowHalfY;
			}

			function onDocumentTouchStart( event ) {

				if ( event.touches.length == 1 ) {

					event.preventDefault();

					mouseX = event.touches[ 0 ].pageX - windowHalfX;
					mouseY = event.touches[ 0 ].pageY - windowHalfY;
				}
			}

			function onDocumentTouchMove( event ) {

				if ( event.touches.length == 1 ) {

					event.preventDefault();

					mouseX = event.touches[ 0 ].pageX - windowHalfX;
					mouseY = event.touches[ 0 ].pageY - windowHalfY;
				}
			}

			//

			function animate() {

				requestAnimationFrame( animate );

				render();
				stats.update();

			}

			function render() {

				camera.position.x += ( mouseX - camera.position.x ) * 0.05;
				camera.position.y += ( - mouseY - camera.position.y ) * 0.05;

				group.rotation.x += 0.01;
				group.rotation.y += 0.02;

				renderer.render( scene, camera );

			}

		</script>