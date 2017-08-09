# vim: set fileencoding=utf-8 :

from werkzeug.routing import BaseConverter


class RegexConverter(BaseConverter):

    def __init__(self, map, *args):
        # print('\nmap:\n{0}\nargs:\n{1}\n'.format(map, args))
        self.map = map
        self.regex = args[0]


def route_handler(app, enable_regex=True):
    if enable_regex:
        # print('enable_regex')
        app.url_map.converters['regex'] = RegexConverter

# vim:ts=4:sw=4
