# vim: set fileencoding=utf-8 :
from .. import app
from flask import Blueprint, render_template, abort

About = Blueprint('about', __name__, url_prefix='/about')


@About.route('/<doc>', methods=['GET'])
def index(doc):
    if doc not in ('privacy', 'disclaimer', 'sla', 'huimeibest'):
        abort(404)
    doc = 'about/{0}.html'.format(doc)
    return render_template(doc)


# vim:ts=4:sw=4
