# time-space.kr 홈페이지 빌더
### 핵심기능(아래)
#### 워드프레스처럼 소스 코어를 버튼으로 업데이트 받는 소스를 만들기(아래)
- 워드프레스처럼 관리자 화면에서 버튼 클릭 한 번으로 소스 코드를 최신 버전으로 업데이트하는 기능(Self-Updating System)을 구현하기위해 서버 측(PHP)에서 파일을 다운로드, 압축 해제, 덮어쓰기하는 로직이 추가함.핵심은 [버전 확인] -> [최신 소스 다운로드] -> [기존 파일 덮어쓰기] 과정을 자동화하였음.
- time-space.kr 빌더 버전업데이트 저장소 https://github.com/time-space-app/kr-update.git
- time-space.kr 빌더 작업 중인 저장소 https://github.com/time-space-app/kr.git
#### 몽9에디터를 홈페이지 빌더용으로 사용(아래)
- 몽9에디터에서 지도를 추가하면, 구글 지도가 표시되는 작업을 했음(데모키 발급URL) : https://mapsplatform.google.com/intl/ko_kr/maps-demo-key/
- 디자인 에디터(Mong9)와 코어 에디터(Ace)로 분리 후 디자인 에디터에서 Tiny파일매니저의 테마가 light 일 때 ckeditor4 를 dark 테마일 때 smarteditor2 를 사용하도록 추가함.
### 사용된 오픈소스 정보(아래)
#### Tiny 파일매니저 포함(아래)
- https://tinyfilemanager.github.io/ , https://github.com/prasathmani/tinyfilemanager (GNU General Public License v3.0)
- 디자인_폰트어썸 아이콘 정보 : https://fontawesome.com/v4/icons/
#### CK 웹 에디터와 파일업로드 및 디자인템플릿 기능 포함된 몽9에디터 포함(아래)
- https://ckeditor.com/ckeditor-4/download/#ckeditor-4 (오픈소스인 v4.22.1 사용 GNU General Public License v3.0 배포)
- v4.22.1 Basic Package (4.22.* 이하 버전은 오픈 소스 라이선스로 무료 사용이 가능, 아래 정보URL)
- https://ckeditor.com/docs/ckeditor4/latest/support/licensing/license-and-legal.html
- https://www.mong9editor.com/index.cgi?page_code=otherpage&code=editor_ckeditor4
-https://github.com/mong9/ckeditor4-plugin-mong9-editor (GNU General Public License v3.0)
#### 네이버 SmartEditor2 웹 에디터와 파일업로드 및 디자인템플릿 기능 포함된 몽9에디터 포함(아래)
- https://github.com/naver/smarteditor2/releases (v2.10.0 사용, 네이버오프소스)
- https://www.mong9editor.com/index.cgi?page_code=otherpage&code=editor_smarteditor2
- https://github.com/mong9/smarteditor2-plugin-mong9-editor (GNU General Public License v3.0)

#### 현재 작업 중인 내용(아래)
- 메뉴는 템플릿 디자인이 없기 때문에 1개만 별도 제작 중.

#### 앞으로 작업예정(아래)
- 몽9에디터의 템플릿디자인과 연동도는 구글지도맵, 게시판, 메뉴까지 _module.php 로 별도로 분리한다.
- 매뉴얼 작업

#### 작업내역 정리(아래)
- RestAPI+Ajax 방식으로 index.php 1페이지에서 CRUD를 작업완료
- mysql 기반의 1페이지 게시판을 작업 중이며, 현재 게시판 DB테이블과 CRUD 반응형 디자인을 작성하였음. 페이징 처리 추가

### 개발환경(아래)
#### 도커 데스크탑의 컨테이너로 개발환경을 사용(아래)
- 작업PC에서 도커데스크탑을 설치하고, 도커컴포즈파일인 docker-compose.yml 로 PHP8.4 과 mysql8.0 기반의 개발환경을 만든다.
- 아래 2개 파일을 같은 폴더에 만들고, docker-compose up -d --build 를 실행한다.(반대로 삭제명령은 docker-compose down 이다.)
- Dockerfile 로 컨테이너에 들어갈 PHP환경의 이미지를 생성하는 파일을 만든다.(아래)
```
FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    libjpeg-dev \
    libfreetype6-dev
	
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd
RUN docker-php-ext-install mysqli

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && a2enmod rewrite
```
- 도커컴포즈파일인 docker-compose.yml 내부의 build 명령으로 위 이미지를 만들고 사용하는 컨테이너를 만드는 파일을 만든다.(아래)
```
version: '1.0'
services:
  app:
    build: .
    container_name: php84-app
    restart: no
    volumes:
      - ./src:/var/www/html
    ports:
      - "80:80"
    depends_on:
      - db
    networks:
      - php-mysql-net

  db:
    image: mysql:8.0
    container_name: mysql8-db
    restart: no
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: mydatabase
      MYSQL_USER: myuser
      MYSQL_PASSWORD: mypassword
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - php-mysql-net

networks:
  php-mysql-net:
    driver: bridge

volumes:
  db_data: 
```
- 위 소스를 만들고, 컨테이너를 실행하는 내용은 제 블로그에서 참조 할 수 있다. ( https://kimilguk.tistory.com/927 )
- 업로드와 같은 소스 권한필요 시 해당 컨테이너의 콘솔화면에서 다음 명령으로 웹 허용권한을 줄 수 있다. chown -R www-data:www-data /var/www/html
- MS클라우드인 Azure 쿠버네티스(AKS)를 사용해도 동일하게 개발환경을 구성할 수 있다. 관련기술 참조. ( https://kimilguk.tistory.com/929 )
