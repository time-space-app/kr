# time-space kr 홈페이지 빌더
### 폴더와 파일구조 및 사용방법(아래)
#### 한눈에 보는 폴더와 파일구조(아래)
```
├── board_upload/ (몽9에디터와 연동된 공지사항 게시판의 첨부파일 업로드위치)
├── ckeditor/ (몽9에디터를 플러그인으로 사용할 디자인에디터 플러그인폴더만 추가 후 수정없는 원본 오픈소스사용)
└── core/ (홈페이지 빌드의 핵심코드인 티니파일매니저v2.6 오픈소스 위치)
    └── _footer.php(몽9에디터가 포함된 디자인 에디터에서 저장시 자동으로 추가되는 페이지 푸터파일)
    └── _footer-module.php(몽9에디터가 포함된 디자인 에디터에서 저장시 자동으로 추가되는 메뉴와 지도기능의 푸터파일에 포함된 모듈)
    └── _footer-news-letter.php(몽9에디터가 포함된 디자인 에디터에서 저장시 자동으로 추가되는 뉴스레터 기능페이지의 푸터파일)
    └── _header.php(몽9에디터가 포함된 디자인 에디터에서 저장시 자동으로 추가되는 페이지 헤더파일)
    └── _header-module.php(몽9에디터가 포함된 디자인 에디터에서 저장시 자동으로 추가되는 RestAPI게시판 기능의 헤더파일에 포함된 모듈)
    └── _header-news-letter.php(몽9에디터가 포함된 디자인 에디터에서 저장시 자동으로 추가되는 뉴스레터 기능페이지의 헤더파일)
    └── adminer.php(DB관리자 로그인 후 1개의 파일에 DB관리에 대한 모든 기능이 포함되어 있다. 외부오픈소스사용)
    └── board-api.php(RestAPI게시판 백엔드 CRUD처리 소스)
    └── config.txt(티니파일매니저 환경설정 파일로 내부소스에서 admin, user암호만 변경 후 config.php로 파일확장자명만 변경후 빌더를 실행한다.)
    └── core-update.php(파일관리자로 로그인 후 현재core폴더의 소스를 타임스페이스 kr빌더 깃 저장소의 소스와 비교 후 최신 소스로 업데이트 할 수 있다.)
    └── env.txt(타임스페이스 kr빌더의 환경설정 파일로 DB와 구글지도를 사용한다면 해당 정보를 입력 후 env.php로 파일확장자명만 변경후 빌더를 실행한다.)
    └── file-download.php(RestAPI게시판 백엔드 첨부파일 처리 소스)
    └── phpinfo.php(파일관리자로 로그인 후  현재 서버의 시스템정보를 확인하는 소스)
    └── proxy.php(구글지도를 사용할 때 API키를 백앤드에서 호출하여 API키 노출을 방지하는 소스)
    └── pwd.html(티니파일매니저 환경설정 파일로 내부소스에서 admin, user암호만 변경할 때 신규암호를 생성하는 UI화면)
    └── pwd-api.php(티니파일매니저 환경설정 파일로 내부소스에서 admin, user암호만 변경할 때 신규암호를 생성하는 백엔드 처리 소스)
    └── tinyfilemanager.org(티니파일매니저 원본 오픈소스. 아래 타임스페이스 kr빌더에서 변경한 tinyfilemanager.php 파일과 비교 시 확인용으로 사용한다.)
    └── tinyfilemanager.php(파일관리자로 로그인 후 1개의 파일에 실행소스가 모두 포함되어 있다. 수정된 오픈소스사용)
├── smarteditor2/ (몽9에디터를 플러그인으로 사용할 디자인에디터에 플러그인폴더만 추가 후 수정없는 원본 오픈소스사용)
├── index.php (제공된 몽9에디터로 제작된 샘플 메인페이지)
├── index-temp.html (제공된 페이지준비중 안내 페이지)
├── LICENSE (타임스페이스 kr빌더의 GPL-3.0 라이센스내용)
├── news-letter.php (제공된 몽9에디터로 제작된 뉴스레터 게시판연동 샘플 페이지. 단, 제작 시 티니파일매니저의 타크테마 선택 후 스마트에디터로 작업해야 한다.)
```
#### 정말 간단한 사용방법(아래)
- 현재 깃 허브에서 최신 버전이 포함된 ZIP 파일을 다운로드하세요.
- 다운로드 후 core 폴더내부의 파일 이름은 바꾸지 마세요. 타임스페이스 kr빌더를 관리자화면에서 최신 버전으로 업데이트 할 수 없게 됩니다.
- 기본 사용자 이름/비밀번호: admin/admin@123 및 user/12345 입니다.(아래)
- 암호를 변경 하려면 아래 방법을 사용하세요.
> [!WARNING]
> 경고: 사용 전에 비밀번호를 직접 생성하십시오 PHP의 password_hash()로 암호를 생성하려면 [여기](https://tinyfilemanager.github.io/docs/pwd.html) 를 클릭하세요.
> [!WARNING]
> 위 생성된 암호를 설정파일에서 사용하려면 [config.txt](https://github.com/time-space-app/kr/blob/v0.9/core/config.txt) 파일을 config.php 파일로 변경 후 admin과 user 암호를 입력합니다.(아래)
```
$auth_users = array(
    'admin' => '$2y$10$/K.hjNr84lLNDt8fTXjoI.DBp6PpeyoJ.mGwrrLuCZfAwfSAGqhOW', //admin@123
    'user' => '$2y$10$Fg6Dz8oH9fPoZ2jJan5tZuv6Z4Kp7avtQ9bDfrdRntXtPeiMAZyGO' //12345
);
```
- DB접속 정보를 변경 하려면 아래 방법을 사용하세요
:information_source: DB 설정파일을 사용하려면 [env.txt](https://github.com/time-space-app/kr/blob/v0.9/core/env.txt) 파일을 env.php 파일로 변경 후 접속정보와 AIP키를 입력합니다.(아래)
```
<?php
//본인의 정보를 이곳에 입력 후 파일명을 env.php로 수정하면 됩니다.
return [
    'DB_HOST' => 'db',
    'DB_USER' => 'myuser',
    'DB_PASS' => 'mypassword',
    'DB_NAME' => 'mydatabase',
    'GOOGLE_API_KEY' => '구글맵데모키',
    'UPLOAD_DIR' => $_SERVER['DOCUMENT_ROOT'].'/board_upload/',
];
?>
```

### 핵심기능(아래)
#### 워드프레스처럼 소스 코어를 버튼으로 업데이트 받는 소스를 만들기(아래)
- 워드프레스처럼 관리자 화면에서 버튼 클릭 한 번으로 소스 코드를 최신 버전으로 업데이트하는 기능(Self-Updating System)을 구현하기위해 서버 측(PHP)에서 파일을 다운로드, 압축 해제, 덮어쓰기하는 로직이 추가함.핵심은 [버전 확인] -> [최신 소스 다운로드] -> [기존 파일 덮어쓰기] 과정을 자동화하였음.
- time-space kr 빌더 버전업데이트 저장소 https://github.com/time-space-app/kr-update.git
- time-space kr 빌더 작업 중인 저장소 https://github.com/time-space-app/kr.git
#### 몽9에디터를 홈페이지 빌더용으로 사용(아래)
- 몽9에디터에서 지도를 추가하면, 구글 지도가 표시되는 작업을 했음(데모키 발급URL) : https://mapsplatform.google.com/intl/ko_kr/maps-demo-key/
- 디자인 에디터(Mong9)와 코어 에디터(Ace)로 분리 후 디자인 에디터에서 Tiny파일매니저의 테마가 light 일 때 ckeditor4 를 dark 테마일 때 smarteditor2 를 사용하도록 추가함.
- 스마트에디터로 뉴스레터용 디자인 메인파일을 만들고 news-letter.php 샘플제작. 단, 반드시 다크테마로 변경 후 뉴스레터 메인 디자인을 만들어야 뉴스레터 게시판과 자동연동 됩니다.
#### 앞으로 작업예정(아래)
- 코어에다터로 재고조사.php 만들기
- 도메인 호스팅에 배포
#### 작업내역 정리(아래)
- 메뉴는 템플릿 디자인이 없기 때문에 1개만 별도 제작 후 -module.php 에 포함(메뉴에 페이지 번역기능 추가).
- 몽9에디터의 템플릿디자인과 연동되는 구글지도맵, 게시판, 메뉴까지 -module.php 로 별도로 분리한다.
- RestAPI+Ajax 방식으로 index.php 1페이지에서 CRUD를 작업완료
- mysql 기반의 1페이지 게시판을 작업 중이며, 현재 게시판 DB테이블과 CRUD 반응형 디자인을 작성하였음. 페이징 처리 추가

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
#### Adminer DB매니저 포함(아래)
- https://www.adminer.org/ (오픈소스인 v5.4.2 사용 LICENSE-2.0 Apache License, GNU General Public License v2.0 배포)

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
