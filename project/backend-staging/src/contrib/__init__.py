# vim: set fileencoding=utf-8 :

from flask import Flask, g, request, Blueprint, abort
from werkzeug.exceptions import HTTPException
from settings.config import running_model
from utils.router import route_handler
from utils import helper
from utils import render
import mongoengine.errors

class SecurityRisk(HTTPException):
    code = 4011
    description = '<p>You will pay for this!</p>'
abort.mapping[4011] = SecurityRisk

app = Flask(__name__, static_url_path='/static', static_folder='../assets/static', template_folder='templates')
app.config.from_object(running_model())
app.config.from_object('instance.config')
route_handler(app, enable_regex=True)

from flask.ext.cache import Cache
cache = Cache(app, config={'CACHE_TYPE': 'simple'})

app.jinja_env.filters['utc_datetime'] = helper.utc_datetime_filter
app.jinja_env.filters['utc_datetime_human'] = helper.utc_datetime_human_filter

SERVICE_TYPE = app.config.get('SERVICE_TYPE')

from .models import db
db.init_app(app)


def display(data, status=0, message=None):
    if message is None:
        message = g.get('message', None)
    return render.jsonify(data, status, message)


@app.errorhandler(409)
def _error_force_show_message(error):
    # app.logger.error('{0} request: {1}'.format(error, request))
    # filters = set(['get_json', 'method', 'mimetype', 'remote_addr', 'path', 'query_string'])
    # helper.insight(request, run_functions=True, filters=filters)
    message = g.get('message', '请重试')
    status = g.get('status', 900)
    return display(None, status, message), 200


@app.errorhandler(400)
def _error_bad_request(error):
    # app.logger.error('{0} request: {1}'.format(error, request))
    # filters = set(['get_json', 'method', 'mimetype', 'remote_addr', 'path', 'query_string'])
    # helper.insight(request, run_functions=True, filters=filters)
    message = g.get('message', 'Bad Request')
    return display(None, 400, message), 200


@app.errorhandler(401)
def _error_unauthorized(error):
    message = g.get('message', 'Unauthorized')
    return display(None, 401, message), 200


@app.errorhandler(4011)
def _error_security_risk(error):
    message = g.get('message', 'Security Risk')
    return display(None, 4011, message), 200


@app.errorhandler(403)
def _error_forbidden(error):
    message = g.get('message', 'Forbidden')
    return display(None, 403, message), 200


@app.errorhandler(404)
def _error_not_found(error):
    # app.logger.error('{0} request: {1}'.format(error, request))
    # helper.insight(request, set(['get_json', 'method', 'mimetype', 'remote_addr', 'path', 'query_string']))
    if g.scene == 'rpc':
        message = g.get('message', 'Not Found')
        return display(None, 404, message), 200
    return 'NOT FOUND', 404


@app.errorhandler(405)
def _errornot_allowed(error):
    return display(None, 405, 'Method Not Allowed'), 200


@app.errorhandler(410)
def _error_gone(error):
    return display(None, 410, 'Gone'), 200


@app.errorhandler(413)
def _error_too_large(error):
    return display(None, 413, 'REQUEST ENTITY TOO LARGE'), 200


@app.errorhandler(mongoengine.errors.NotUniqueError)
@app.errorhandler(mongoengine.errors.ValidationError)
@app.errorhandler(500)
def _error_server_error(error):
    message = g.get('message', str(error))
    return display(None, 500, message), 200

from .views import __coldplug__

# print(app.url_map)
# print(loader.loaded(__name__, detail=True))

app.logger.info('running mode: {0}{1}'.format(app.config.get('RUN_MODE'), '\n'*8))

# vim:ts=4:sw=4
