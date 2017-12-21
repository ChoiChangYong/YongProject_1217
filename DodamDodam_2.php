<!DOCTYPE html>

<?php
	ini_set('memory_limit','512M');
	$conn = mysqli_connect("localhost", "root", 111111);
	if (!$conn)
	{
	  $error = mysqli_connect_error();
	  $errno = mysqli_connect_errno();
	  print "$errno: $error\n";
	  exit();
	}
	mysqli_select_db($conn, "childhouse");
	$result_child_house = mysqli_query($conn, "SELECT * FROM `child_house`");
	$result_childprtc = mysqli_query($conn, "SELECT * FROM `childprtc`");
	$result_frequentzonechild = mysqli_query($conn, "SELECT * FROM `frequentzonechild`");
	$result_hospital = mysqli_query($conn, "SELECT * FROM `hospital`");
	$result_park = mysqli_query($conn, "SELECT * FROM `park`");
?>

<html>
	<head>
	<meta charset="utf-8"/>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
		<title>Daum Map</title>
		<!--<link rel="stylesheet" type="text/css" href="http://localhost/style_child.css">-->
		<style>
		.map_wrap, .map_wrap * {margin:0;padding:0;font-family:'Malgun Gothic',dotum,'돋움',sans-serif;font-size:12px;}
		.map_wrap a, .map_wrap a:hover, .map_wrap a:active{color:#000;text-decoration: none;}
		.map_wrap {position:relative;width:100%;height:800px;}
		#menu_wrap {position:absolute; top:0;left:0;bottom:0;margin:10px 0 30px 10px;padding:5px;overflow-y:auto;background:rgba(255, 255, 255, 0.7);z-index: 1;font-size:12px;border-radius: 10px;}
		.bg_white {background:#fff;}
		#menu_wrap hr {display: block; height: 1px;border: 0; border-top: 2px solid #5F5F5F;margin:3px 0;}
		#menu_wrap .option{text-align: center;}
		#menu_wrap .option p {margin:10px 0;}
		#menu_wrap .option button {margin-left:5px;}
		#placesList li {list-style: none;}
		#placesList .item {position:relative;border-bottom:1px solid #888;overflow: hidden;cursor: pointer;min-height: 65px;}
		#placesList .item span {display: block;margin-top:4px;}
		#placesList .item h5, #placesList .item .info {text-overflow: ellipsis;overflow: hidden;white-space: nowrap;}
		#placesList .item .info{padding:10px 0 10px 55px;}
		#placesList .info .gray {color:#8a8a8a;}
		#placesList .info .jibun {padding-left:26px;background:url(http://t1.daumcdn.net/localimg/localimages/07/mapapidoc/places_jibun.png) no-repeat;}
		#placesList .info .tel {color:#009900;}
		#placesList .item .markerbg {float:left;position:absolute;width:36px; height:37px;margin:10px 0 0 10px;}
		#placesList .item .marker_1 {background:url(http://localhost/Dodam_image/map_ico1.png) no-repeat;}
		#placesList .item .marker_2 {background:url(http://localhost/Dodam_image/map_ico2.png) no-repeat;}
		#placesList .item .marker_3 {background:url(http://localhost/Dodam_image/map_ico3.png) no-repeat;}
		#placesList .item .marker_4 {background:url(http://localhost/Dodam_image/map_ico4.png) no-repeat;}
		#placesList .item .marker_5 {background:url(http://localhost/Dodam_image/map_ico5.png) no-repeat;}
		#pagination {margin:10px auto;text-align: center;}
		#pagination a {display:inline-block;margin-right:10px;}
		#pagination .on {font-weight: bold; cursor: default;color:#777;}



		.info_circle {position:relative;top:5px;left:5px;border-radius:6px;border: 1px solid #ccc;border-bottom:2px solid #ddd;font-size:12px;padding:5px;background:#fff;list-style:none;margin:0;}
		.info_circle:nth-of-type(n) {border:0; box-shadow:0px 1px 2px #888;}
		.info_circle .label {display:inline-block;width:50px;}
		.number {font-weight:bold;color:#00a0e9;}
	</style>

    <body>
			<div class="map_wrap">
    		<div id="map" style="width:100%;height:100%;position:relative;overflow:hidden;"></div>

					<div id="menu_wrap" class="bg_white">
						<div>
                    <button type="button" onclick="searchPlaces();">계산시작!</button>
            </div>
						<ul id="placesList"></ul>
		        <div id="pagination"></div>
		   		</div>
			</div>

		<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=2004735008028d57705d13f4a8222662">
		</script>

		<script type="text/javascript">
			var mapContainer = document.getElementById('map'), // 지도를 표시할 div
			mapOption = {
				center: new daum.maps.LatLng(35.143165, 129.034182), // 지도의 중심좌표
				level: 5 // 지도의 확대 레벨
			};

			var map = new daum.maps.Map(mapContainer, mapOption); // 지도를 생성합니다
			// 지도 타입 변경 컨트롤을 생성한다
			var mapTypeControl = new daum.maps.MapTypeControl();

			// 검색 결과 목록이나 마커를 클릭했을 때 장소명을 표출할 인포윈도우를 생성합니다
			var infowindow = new daum.maps.InfoWindow({zIndex:1});

			// 지도의 상단 우측에 지도 타입 변경 컨트롤을 추가한다
			map.addControl(mapTypeControl, daum.maps.ControlPosition.TOPRIGHT);

			// 지도에 확대 축소 컨트롤을 생성한다
			var zoomControl = new daum.maps.ZoomControl();

			// 지도의 우측에 확대 축소 컨트롤을 추가한다
			map.addControl(zoomControl, daum.maps.ControlPosition.RIGHT);

			var drawingFlag = false; // 원이 그려지고 있는 상태를 가지고 있을 변수입니다
			var centerPosition; // 원의 중심좌표 입니다
			var drawingCircle; // 그려지고 있는 원을 표시할 원 객체입니다
			var drawingOverlay; // 그려지고 있는 원의 반경을 표시할 커스텀오버레이 입니다
			var drawingDot; // 그려지고 있는 원의 중심점을 표시할 커스텀오버레이 입니다

			var circles = []; // 클릭으로 그려진 원과 반경 정보를 표시하는 선과 커스텀오버레이를 가지고 있을 배열입니다
			var marker_child_house = [];
			var positions_child_house = [];

			var marker_frequentzonechild = [];
			var positions_frequentzonechild = [];

			var marker_hospital = [];
			var positions_hospital = [];

			var marker_childprtc = [];
			var positions_childprtc = [];

			var marker_park = [];
			var positions_park = [];

			var markers = [];

			// 마커의 개수를 나타내는 변수입니다.
			var num_child_house = 0;
			var num_frequentzonechild = 0;
			var num_park = 0;
			var num_hospital = 0;
			var num_childprtc = 0;


			// 마커가 표시될 위치입니다
			var markerPosition = mapOption.center;
			// 마커를 생성합니다
			var marker_main = new daum.maps.Marker({
			    position: markerPosition
			});
			// 마커가 지도 위에 표시되도록 설정합니다
			marker_main.setMap(map);

			///////////////////////////////////////////////////////////////////////
			// 마커를 표시할 위치와 title 객체 배열입니다
			positions_child_house = [
				{
						title: " ",
						latlng: new daum.maps.LatLng(0, 0)
				}
			];
			positions_frequentzonechild = [
				{
						title: " ",
						latlng: new daum.maps.LatLng(0, 0)
				}
			];
			positions_childprtce = [
				{
						title: " ",
						latlng: new daum.maps.LatLng(0, 0)
				}
			];
			positions_hospital = [
				{
						title: " ",
						latlng: new daum.maps.LatLng(0, 0)
				}
			];
			positions_park = [
				{
						title: " ",
						latlng: new daum.maps.LatLng(0, 0)
				}
			];
			<?php
			  /////////////////////////////////////////////////
			  //어린이집
				/////////////////////////////////////////////////
				$x_array_child_house = array();
				$y_array_child_house = array();
				$name_child_house = array();
				$addrRoad_child_house = array();
				$phone_child_house = array();

				$count_child_house = 0;
				while( $row = mysqli_fetch_assoc($result_child_house))
				{
					$x_array_child_house[$count_child_house] = $row['longitude'];
					$y_array_child_house[$count_child_house] = $row['latitude'];
					$name_child_house[$count_child_house] = $row['name'];
					$addrRoad_child_house[$count_child_house] = $row['addrRoad'];
					$phone_child_house[$count_child_house] = $row['phone'];
					$count_child_house++;
				}
			?>

			var name_child_house = <?php echo json_encode($name_child_house, JSON_UNESCAPED_UNICODE)?>;
			var x_child_house = <?php echo json_encode($x_array_child_house)?>;
			var y_child_house = <?php echo json_encode($y_array_child_house)?>;
			var addrRoad_child_house = <?php echo json_encode($addrRoad_child_house)?>;
			var phone_child_house = <?php echo json_encode($phone_child_house)?>;

			for(var i = 0; i < <?php echo $count_child_house ?>; i++)
			{
				positions_child_house[i] =
				{
						title: name_child_house[i],
						latlng: new daum.maps.LatLng(x_child_house[i], y_child_house[i]),
						addr: addrRoad_child_house[i],
						phone: phone_child_house[i]
				};
			}

			<?php
				/////////////////////////////////////////////////
				//어린이 보행자 사고 다발지역
				/////////////////////////////////////////////////
				$x_array_frequentzonechild = array();
				$y_array_frequentzonechild = array();
				$name_frequentzonechild = array();

				$count_frequentzonechild = 0;
				while( $row = mysqli_fetch_assoc($result_frequentzonechild))
				{
					$x_array_frequentzonechild[$count_frequentzonechild] = $row['YPos'];
					$y_array_frequentzonechild[$count_frequentzonechild] = $row['XPos'];
					$name_frequentzonechild[$count_frequentzonechild] = $row['name'];
					$count_frequentzonechild++;
				}
			?>
			var name_frequentzonechild = <?php echo json_encode($name_frequentzonechild, JSON_UNESCAPED_UNICODE)?>;
			var x_frequentzonechild = <?php echo json_encode($x_array_frequentzonechild)?>;
			var y_frequentzonechild = <?php echo json_encode($y_array_frequentzonechild)?>;

			for(var i = 0; i < <?php echo $count_frequentzonechild ?>; i++)
			{
				positions_frequentzonechild[i] =
				{
						title: name_frequentzonechild[i],
						latlng: new daum.maps.LatLng(x_frequentzonechild[i], y_frequentzonechild[i])
				};
			}

			<?php
				/////////////////////////////////////////////////
				//소아과가 있는 병원
				/////////////////////////////////////////////////
				$x_array_hospital = array();
				$y_array_hospital = array();
				$name_hospital = array();
				$addr_hospital = array();
				$telno_hospital = array();

				$count_hospital = 0;
				while( $row = mysqli_fetch_assoc($result_hospital))
				{
					$x_array_hospital[$count_hospital] = $row['YPos'];
					$y_array_hospital[$count_hospital] = $row['XPos'];
					$name_hospital[$count_hospital] = $row['yadmNm'];
					$addr_hospital[$count_hospital] = $row['addr'];
					$telno_hospital[$count_hospital] = $row['telno'];
					$count_hospital++;
				}
			?>
			var name_hospital = <?php echo json_encode($name_hospital, JSON_UNESCAPED_UNICODE)?>;
			var x_hospital = <?php echo json_encode($x_array_hospital)?>;
			var y_hospital = <?php echo json_encode($y_array_hospital)?>;
			var addr_hospital = <?php echo json_encode($addr_hospital)?>;
			var telno_hospital = <?php echo json_encode($telno_hospital)?>;

			for(var i = 0; i < <?php echo $count_hospital ?>; i++)
			{
				positions_hospital[i] =
				{
						title: name_hospital[i],
						latlng: new daum.maps.LatLng(x_hospital[i], y_hospital[i]),
						addr: addr_hospital[i],
						phone: telno_hospital[i]
				};
			}

			<?php
				/////////////////////////////////////////////////
				//어린이 보호구역
				/////////////////////////////////////////////////
				$x_array_childprtc = array();
				$y_array_childprtc = array();
				$name_childprtc = array();

				$count_childprtc = 0;
				while( $row = mysqli_fetch_assoc($result_childprtc))
				{
					$x_array_childprtc[$count_childprtc] = $row['longitude'];
					$y_array_childprtc[$count_childprtc] = $row['latitude'];
					$name_childprtc[$count_childprtc] = $row['name'];
					$addrRoad_childprtc[$count_childprtc] = $row['addr'];
					$cctv_childprtc[$count_childprtc] = $row['cctvNum'];
					$count_childprtc++;
				}
			?>

			var name_childprtc = <?php echo json_encode($name_childprtc, JSON_UNESCAPED_UNICODE)?>;
			var x_childprtc = <?php echo json_encode($x_array_childprtc)?>;
			var y_childprtc = <?php echo json_encode($y_array_childprtc)?>;
			var addrRoad_childprtc = <?php echo json_encode($addrRoad_childprtc)?>;
			var cctv_childprtc = <?php echo json_encode($cctv_childprtc)?>;

			for(var i = 0; i < <?php echo $count_childprtc ?>; i++)
			{
				positions_childprtc[i] =
				{
						title: name_childprtc[i],
						latlng: new daum.maps.LatLng(x_childprtc[i], y_childprtc[i]),
						addr: addrRoad_childprtc[i],
						cctv: cctv_childprtc[i]
				};
			}

			<?php
			  /////////////////////////////////////////////////
			  //공원
				/////////////////////////////////////////////////
				$x_array_park = array();
				$y_array_park = array();
				$name_park = array();
				$addrRoad_park = array();
				$phone_park = array();

				$count_park = 0;
				while( $row = mysqli_fetch_assoc($result_park))
				{
					$x_array_park[$count_park] = $row['longitude'];
					$y_array_park[$count_park] = $row['latitude'];
					$name_park[$count_park] = $row['name'];
					$addrRoad_park[$count_park] = $row['addr'];
					$phone_park[$count_park] = $row['phone'];
					$count_park++;
				}
			?>

			var name_park = <?php echo json_encode($name_park, JSON_UNESCAPED_UNICODE)?>;
			var x_park = <?php echo json_encode($x_array_park)?>;
			var y_park = <?php echo json_encode($y_array_park)?>;
			var addrRoad_park = <?php echo json_encode($addrRoad_park)?>;
			var phone_park = <?php echo json_encode($phone_park)?>;

			for(var i = 0; i < <?php echo $count_park ?>; i++)
			{
				positions_park[i] =
				{
						title: name_park[i],
						latlng: new daum.maps.LatLng(x_park[i], y_park[i]),
						addr: addrRoad_park[i],
						phone: phone_park[i]
				};
			}

			var listEl;

					listEl = document.getElementById('placesList'),
					menuEl = document.getElementById('menu_wrap');
			// 지도 확대 레벨 변화 이벤트를 등록한다
					daum.maps.event.addListener(map, 'zoom_changed', function () {
						if(map.getLevel()>=6)
						{
							//alert(map.getLevel());
							removeCircles();
						}
					});

					// 검색 결과 목록과 마커를 표출하는 함수입니다
					function displayPlaces() {

					    fragment = document.createDocumentFragment(),
					    //bounds = new daum.maps.LatLngBounds(),
					    listStr = '';
							num_child_house = 0;
							num_frequentzonechild = 0;
							num_park = 0;
							num_hospital = 0;
							num_childprtc = 0;

					    // 검색 결과 목록에 추가된 항목들을 제거합니다
					    removeAllChildNods(listEl);
							// 지도에 표시되고 있는 마커를 제거합니다
					    //removeMarker();

					    menuEl.scrollTop = 0;

					    // 검색된 장소 위치를 기준으로 지도 범위를 재설정합니다
					    //map.setBounds(bounds);
					}
					// 검색결과 항목을 Element로 반환하는 함수입니다
					function getListItemTotal(num_child_house, num_frequentzonechild, num_park, num_hospital, num_childprtc) {

							var el = document.createElement('li'),
					    itemStr = '<span class="markerbg marker_' + '1' + '"></span>' +
					              '<div class="info">' +
					              '   <h5>' + '원 안에 어린이집이 ' + num_child_house + '개 있습니다.' + '</h5>' +
												'</div>';

							itemStr += '<span class="markerbg marker_' + '2' + '"></span>' +
											  '<div class="info">' +
											  '   <h5>' + '원 안에 어린이 사고다발지역이 ' + num_frequentzonechild + '개 있습니다.' + '</h5>' +
												'</div>';

							itemStr += '<span class="markerbg marker_' + '3' + '"></span>' +
												'<div class="info">' +
												'   <h5>' + '원 안에 공원이 ' + num_park + '개 있습니다.' + '</h5>' +
												'</div>';

							itemStr += '<span class="markerbg marker_' + '4' + '"></span>' +
												'<div class="info">' +
												'   <h5>' + '원 안에 소아 진료가 가능한 병원이 ' + num_hospital + '개 있습니다.' + '</h5>' +
												'</div>';

							itemStr += '<span class="markerbg marker_' + '5' + '"></span>' +
												'<div class="info">' +
												'   <h5>' + '원 안에 어린이 보호구역이 ' + num_childprtc + '개 있습니다.' + '</h5>' +
												'</div>';
							itemStr += '<br />';
					    el.innerHTML = itemStr;
					    el.className = 'item';

					    return el;
					}
					function getListItem(places, flag) {

					    var el = document.createElement('li'),
					    itemStr = '<span class="markerbg marker_' + (flag) + '"></span>' +
					                '<div class="info">' +
					                '   <h5>' + places.title + '</h5>';

					    if (places.addr) {
					        itemStr += '    <span>' + places.addr + '</span>';
					    }
							if (places.phone) {
					      itemStr += '  <span class="tel">' + places.phone  + '</span>' +
					                '</div>';
							 }
							if (places.cctv) {
 					      itemStr += '  <span class="tel">' + 'CCVT 개수 : ' + places.cctv  + '</span>' +
 					                '</div>';
 							}
							itemStr += '</div>';
					    el.innerHTML = itemStr;
					    el.className = 'item';

					    return el;
					}

					// 검색결과 목록 하단에 페이지번호를 표시는 함수입니다
					function displayPagination(pagination) {
					    var paginationEl = document.getElementById('pagination'),
					        fragment = document.createDocumentFragment(),
					        i;

					    // 기존에 추가된 페이지번호를 삭제합니다
					    while (paginationEl.hasChildNodes()) {
					        paginationEl.removeChild (paginationEl.lastChild);
					    }

					    for (i=1; i<=pagination.last; i++) {
					        var el = document.createElement('a');
					        el.href = "#";
					        el.innerHTML = i;

					        if (i===pagination.current) {
					            el.className = 'on';
					        } else {
					            el.onclick = (function(i) {
					                return function() {
					                    pagination.gotoPage(i);
					                }
					            })(i);
					        }

					        fragment.appendChild(el);
					    }
					    paginationEl.appendChild(fragment);
					}
					// 검색결과 목록 또는 마커를 클릭했을 때 호출되는 함수입니다
					// 인포윈도우에 장소명을 표시합니다
					function displayInfowindow(marker, title) {
					    var content = '<div style="padding:5px;z-index:1;">' + title + '</div>';

					    infowindow.setContent(content);
					    infowindow.open(map, marker);
					}

					 // 검색결과 목록의 자식 Element를 제거하는 함수입니다
					function removeAllChildNods(el) {
					    while (el.hasChildNodes()) {
					        el.removeChild (el.lastChild);
					    }
					}

					// 마커를 생성하는 함수입니다
					function addMarker(position, markerImage, flag) {
						marker = new daum.maps.Marker({
							position: position.latlng, // 마커를 표시할 위치
							//title : position.title, // 마커의 타이틀, 마커에 마우스를 올리면 타이틀이 표시됩니다
							image : markerImage // 마커 이미지
						});
						marker.setMap(map);
						markers.push(marker);

						itemEl = getListItem(position, flag); // 검색 결과 항목 Element를 생성합니다

						listEl.appendChild(itemEl);

						(function(marker, title) {
							daum.maps.event.addListener(marker, 'mouseover', function() {
									displayInfowindow(marker, title);
							});
							daum.maps.event.addListener(marker, 'mouseout', function() {
									infowindow.close();
							});
							itemEl.onmouseover =  function () {
                displayInfowindow(marker, title);
	            };

	            itemEl.onmouseout =  function () {
	                infowindow.close();
	            };
						})(marker, position.title);

					}

			// 마우스 드래그로 지도 이동이 완료되었을 때 마지막 파라미터로 넘어온 함수를 호출하도록 이벤트를 등록합니다
			daum.maps.event.addListener(map, 'dragend', function() {
					removeCircles();
					marker_main.setMap(null);

			    // 지도 중심좌표를 얻어옵니다
			    var latlng = map.getCenter();

					markerPosition = map.getCenter();
					marker_main = new daum.maps.Marker({
						 position: new daum.maps.LatLng(latlng.getLat(), latlng.getLng())
				  });
				  marker_main.setMap(map);

					num_child_house = 0;
					num_frequentzonechild = 0;
					num_park = 0;
					num_hospital = 0;
					num_childprtc = 0;

			});
			///////////////////////////////////////////////////////////////////////

			function searchPlaces() {
				map.setLevel(4);

				// 원 객체를 생성합니다
				var circle = new daum.maps.Circle({
					center : markerPosition, // 원의 중심좌표입니다
					radius: 500, // 원의 반지름입니다 m 단위 이며 선 객체를 이용해서 얻어옵니다
					strokeWeight: 1, // 선의 두께입니다
					strokeColor: '#00a0e9', // 선의 색깔입니다
					strokeOpacity: 0.1, // 선의 불투명도입니다 0에서 1 사이값이며 0에 가까울수록 투명합니다
					strokeStyle: 'solid', // 선의 스타일입니다
					fillColor: '#00a0e9', // 채우기 색깔입니다
					fillOpacity: 0.2  // 채우기 불투명도입니다
				});

				var radius = Math.round(circle.getRadius()), // 원의 반경 정보를 얻어옵니다
				content = getTimeHTML(radius); // 커스텀 오버레이에 표시할 반경 정보입니다

				rClickPosition = new daum.maps.LatLng(markerPosition.getLat()+0.00187443869, markerPosition.getLng()+0.00641099107)
				// 반경정보를 표시할 커스텀 오버레이를 생성합니다
				var radiusOverlay = new daum.maps.CustomOverlay({
					content: content, // 표시할 내용입니다
					position: rClickPosition, // 표시할 위치입니다. 클릭한 위치로 설정합니다
					xAnchor: 0,
					yAnchor: 0,
					zIndex: 1
				});

				// 원을 지도에 표시합니다
				circle.setMap(map);

				// 반경 정보 커스텀 오버레이를 지도에 표시합니다
				radiusOverlay.setMap(map);

				// 배열에 담을 객체입니다. 원, 선, 커스텀오버레이 객체를 가지고 있습니다
				var radiusObj = {
					'circle' : circle,
					'overlay' : radiusOverlay
				};

				// 배열에 추가합니다
				// 이 배열을 이용해서 "모두 지우기" 버튼을 클릭했을 때 지도에 그려진 원, 선, 커스텀오버레이들을 지웁니다
				circles.push(radiusObj);

				// 그리기 상태를 그리고 있지 않는 상태로 바꿉니다
				drawingFlag = false;

				///////////////////////////////////////////////////////////////////////////////////////////////////
				var rad = Math.PI / 180;
				var latitude = 500 / 6378137;
				latitude = latitude / rad;
				var longitude = latitude / Math.cos(latitude * rad);

				var x_circle_in_identify_child_house = new Array();
				var y_circle_in_identify_child_house = new Array();

				var x_circle_in_identify_frequentzonechild = new Array();
				var y_circle_in_identify_frequentzonechild = new Array();

				var x_circle_in_identify_hospital = new Array();
				var y_circle_in_identify_hospital = new Array();

				var x_circle_in_identify_childprtc = new Array();
				var y_circle_in_identify_childprtc = new Array();

				var x_circle_in_identify_park = new Array();
				var y_circle_in_identify_park = new Array();
				//var radius_circle_in_identify = Math.abs(markerPosition.getLat() + longitude);
				var radius_circle_in_identify = longitude;
				///////////////////////////////////////////////////////////////////////////////////////////////////
				//alert("markerPosition.getLat() = "+markerPosition.getLat());
				//alert("longitude = "+longitude);

				///////////////////////////////////////////////////////////////////////////////////////////////////

				// 마커 이미지의 이미지 주소입니다
				var imageSrc_child_house = "http://localhost/Dodam_image/map_ico1.png";
				var imageSrc_frequentzonechild = "http://localhost/Dodam_image/map_ico2.png";
				var imageSrc_park = "http://localhost/Dodam_image/map_ico3.png";
				var imageSrc_hospital = "http://localhost/Dodam_image/map_ico4.png";
				var imageSrc_childprtc = "http://localhost/Dodam_image/map_ico5.png";

				var markerImage_child_house;
				var markerImage_frequentzonechild;
				var markerImage_park;
				var markerImage_hospital;
				var markerImage_childprtc;

				var imageSize;

				var marker_child_house_count = 0;
				var marker_frequentzonechild_count = 0;
				var marker_park_count = 0;
				var marker_hospital_count = 0;
				var marker_childprtc_count = 0;

				marker_child_house = Array(); //
				marker_frequentzonechild = Array(); //
				marker_park = Array(); //
				marker_hospital = Array(); //
				marker_childprtc = Array(); //

				// 마커 이미지의 이미지 크기 입니다
				imageSize = new daum.maps.Size(24, 35);

				for (var i = 0; i < positions_child_house.length; i ++) {

					x_circle_in_identify_child_house[i] = x_child_house[i]-markerPosition.getLat();
					y_circle_in_identify_child_house[i] = y_child_house[i]-markerPosition.getLng();

					if( (x_circle_in_identify_child_house[i] * x_circle_in_identify_child_house[i]) + (y_circle_in_identify_child_house[i] * y_circle_in_identify_child_house[i]) < (radius_circle_in_identify * radius_circle_in_identify) )
					{
						num_child_house++;
					}
				}
				for (var i = 0; i < positions_frequentzonechild.length; i ++) {

					x_circle_in_identify_frequentzonechild[i] = x_frequentzonechild[i]-markerPosition.getLat();
					y_circle_in_identify_frequentzonechild[i] = y_frequentzonechild[i]-markerPosition.getLng();

					if( (x_circle_in_identify_frequentzonechild[i] * x_circle_in_identify_frequentzonechild[i]) + (y_circle_in_identify_frequentzonechild[i] * y_circle_in_identify_frequentzonechild[i]) < (radius_circle_in_identify * radius_circle_in_identify) )
					{
						num_frequentzonechild++;
					}
				}

				for (var i = 0; i < positions_park.length; i ++) {

					x_circle_in_identify_park[i] = x_park[i]-markerPosition.getLat();
					y_circle_in_identify_park[i] = y_park[i]-markerPosition.getLng();

					if( (x_circle_in_identify_park[i] * x_circle_in_identify_park[i]) + (y_circle_in_identify_park[i] * y_circle_in_identify_park[i]) < (radius_circle_in_identify * radius_circle_in_identify) )
					{
						num_park++;
					}
				}
				for (var i = 0; i < positions_hospital.length; i ++) {

					x_circle_in_identify_hospital[i] = x_hospital[i]-markerPosition.getLat();
					y_circle_in_identify_hospital[i] = y_hospital[i]-markerPosition.getLng();

					if( (x_circle_in_identify_hospital[i] * x_circle_in_identify_hospital[i]) + (y_circle_in_identify_hospital[i] * y_circle_in_identify_hospital[i]) < (radius_circle_in_identify * radius_circle_in_identify) )
					{
						num_hospital++;
					}
				}

				for (var i = 0; i < positions_childprtc.length; i ++) {

					x_circle_in_identify_childprtc[i] = x_childprtc[i]-markerPosition.getLat();
					y_circle_in_identify_childprtc[i] = y_childprtc[i]-markerPosition.getLng();

					if( (x_circle_in_identify_childprtc[i] * x_circle_in_identify_childprtc[i]) + (y_circle_in_identify_childprtc[i] * y_circle_in_identify_childprtc[i]) < (radius_circle_in_identify * radius_circle_in_identify) )
					{
						num_childprtc++;
					}
				}

				itemEl = getListItemTotal(num_child_house, num_frequentzonechild, num_park, num_hospital, num_childprtc);

				listEl.appendChild(itemEl);

				for (var i = 0; i < positions_child_house.length; i ++) {

					x_circle_in_identify_child_house[i] = x_child_house[i]-markerPosition.getLat();
					y_circle_in_identify_child_house[i] = y_child_house[i]-markerPosition.getLng();

					if( (x_circle_in_identify_child_house[i] * x_circle_in_identify_child_house[i]) + (y_circle_in_identify_child_house[i] * y_circle_in_identify_child_house[i]) < (radius_circle_in_identify * radius_circle_in_identify) )
					{
						// 마커 이미지를 생성합니다
						markerImage_child_house = new daum.maps.MarkerImage(imageSrc_child_house, imageSize);

						// 마커를 생성합니다
						addMarker(positions_child_house[i], markerImage_child_house, 1);

					}
				}
				for (var i = 0; i < positions_frequentzonechild.length; i ++) {

					x_circle_in_identify_frequentzonechild[i] = x_frequentzonechild[i]-markerPosition.getLat();
					y_circle_in_identify_frequentzonechild[i] = y_frequentzonechild[i]-markerPosition.getLng();

					if( (x_circle_in_identify_frequentzonechild[i] * x_circle_in_identify_frequentzonechild[i]) + (y_circle_in_identify_frequentzonechild[i] * y_circle_in_identify_frequentzonechild[i]) < (radius_circle_in_identify * radius_circle_in_identify) )
					{
						// 마커 이미지를 생성합니다
						markerImage_frequentzonechild = new daum.maps.MarkerImage(imageSrc_frequentzonechild, imageSize);

						// 마커를 생성합니다
						addMarker(positions_frequentzonechild[i], markerImage_frequentzonechild, 2);

					}
				}

				for (var i = 0; i < positions_park.length; i ++) {

					x_circle_in_identify_park[i] = x_park[i]-markerPosition.getLat();
					y_circle_in_identify_park[i] = y_park[i]-markerPosition.getLng();

					if( (x_circle_in_identify_park[i] * x_circle_in_identify_park[i]) + (y_circle_in_identify_park[i] * y_circle_in_identify_park[i]) < (radius_circle_in_identify * radius_circle_in_identify) )
					{
						// 마커 이미지를 생성합니다
						markerImage_park = new daum.maps.MarkerImage(imageSrc_park, imageSize);

						// 마커를 생성합니다
						addMarker(positions_park[i], markerImage_park, 3);

					}
				}
				for (var i = 0; i < positions_hospital.length; i ++) {

					x_circle_in_identify_hospital[i] = x_hospital[i]-markerPosition.getLat();
					y_circle_in_identify_hospital[i] = y_hospital[i]-markerPosition.getLng();

					if( (x_circle_in_identify_hospital[i] * x_circle_in_identify_hospital[i]) + (y_circle_in_identify_hospital[i] * y_circle_in_identify_hospital[i]) < (radius_circle_in_identify * radius_circle_in_identify) )
					{

						// 마커 이미지를 생성합니다
						markerImage_hospital = new daum.maps.MarkerImage(imageSrc_hospital, imageSize);

						// 마커를 생성합니다
						addMarker(positions_hospital[i], markerImage_hospital, 4);
					}
				}

				for (var i = 0; i < positions_childprtc.length; i ++) {

					x_circle_in_identify_childprtc[i] = x_childprtc[i]-markerPosition.getLat();
					y_circle_in_identify_childprtc[i] = y_childprtc[i]-markerPosition.getLng();

					if( (x_circle_in_identify_childprtc[i] * x_circle_in_identify_childprtc[i]) + (y_circle_in_identify_childprtc[i] * y_circle_in_identify_childprtc[i]) < (radius_circle_in_identify * radius_circle_in_identify) )
					{
						// 마커 이미지를 생성합니다
						markerImage_childprtc = new daum.maps.MarkerImage(imageSrc_childprtc, imageSize);

						// 마커를 생성합니다
						addMarker(positions_childprtc[i], markerImage_childprtc, 5);
					}
				}
				// 중심 좌표를 초기화 합니다
				markerPosition = null;

			}


			// 지도에 표시되어 있는 모든 원과 반경정보를 표시하는 선, 커스텀 오버레이를 지도에서 제거합니다
			function removeCircles() {
				for (var i = 0; i < circles.length; i++) {
					circles[i].circle.setMap(null);
					circles[i].overlay.setMap(null);
				}

				circles = [];

				for (var i = 0; i < markers.length; i ++) {
						markers[i].setMap(null);
				}
				markers = [];

				// 검색 결과 목록에 추가된 항목들을 제거합니다
				if(listEl != null)
					removeAllChildNods(listEl);
			}

			// 마우스 우클릭 하여 원 그리기가 종료됐을 때 호출하여
			// 그려진 원의 반경 정보와 반경에 대한 도보, 자전거 시간을 계산하여
			// HTML Content를 만들어 리턴하는 함수입니다
			function getTimeHTML(distance) {

				// 도보의 시속은 평균 4km/h 이고 도보의 분속은 67m/min입니다
				var walkkTime = distance / 67 | 0;
				var walkHour = '', walkMin = '';

				// 계산한 도보 시간이 60분 보다 크면 시간으로 표시합니다
				if (walkkTime > 60) {
					walkHour = '<span class="number">' + Math.floor(walkkTime / 60) + '</span>시간 '
				}
				walkMin = '<span class="number">' + walkkTime % 60 + '</span>분'

				// 자전거의 평균 시속은 16km/h 이고 이것을 기준으로 자전거의 분속은 267m/min입니다
				var bycicleTime = distance / 227 | 0;
				var bycicleHour = '', bycicleMin = '';

				// 계산한 자전거 시간이 60분 보다 크면 시간으로 표출합니다
				if (bycicleTime > 60) {
					bycicleHour = '<span class="number">' + Math.floor(bycicleTime / 60) + '</span>시간 '
				}
				bycicleMin = '<span class="number">' + bycicleTime % 60 + '</span>분'

				// 거리와 도보 시간, 자전거 시간을 가지고 HTML Content를 만들어 리턴합니다
				var content = '<ul class="info_circle">';

				content += '    <li>';
				content += '        <span class="label">총거리</span><span class="number">' + distance + '</span>m';
				content += '    </li>';
				content += '    <li>';
				content += '        <span class="label">도보</span>' + walkHour + walkMin;
				content += '    </li>';
				content += '    <li>';
				content += '        <span class="label">자전거</span>' + bycicleHour + bycicleMin;
				content += '    </li>';

				content += '</ul>'

				return content;
			}

        </script>
    </head>
	</body>
</html>
