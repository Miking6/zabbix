CREATE TABLE slides (
        slideid         bigint	DEFAULT '0'     NOT NULL,
        slideshowid     bigint 	DEFAULT '0'     NOT NULL,
        delay           integer         DEFAULT '0'     NOT NULL,
        PRIMARY KEY (slideid)
);
CREATE INDEX slides_slides_1 on slides (slideshowid);

