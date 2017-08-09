# vim: set fileencoding=utf-8 :

from contrib import app

if __name__ == '__main__':
    #
    # RUN_MODE=DEVELOPMENT python3 run.py
    #
    # app.run(host='0.0.0.0', port=8089, debug=app.debug, use_reloader=False)
    app.run(host='0.0.0.0', port=8089, debug=app.debug, threaded=True)

# vim:ts=4:sw=4
