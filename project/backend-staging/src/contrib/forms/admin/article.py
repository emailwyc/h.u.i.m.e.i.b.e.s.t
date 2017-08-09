# vim: set fileencoding=utf-8 :

from . import *
from flask_wtf import Form


class UpdateForm(Form):
    articles = TextAreaField('精品<br><br>文章', filters=(strip, ))


# vim:ts=4:sw=4
